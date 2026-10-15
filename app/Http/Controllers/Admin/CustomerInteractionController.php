<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CustomerFollowUp;
use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\User;
use App\Services\Clients\CustomerActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerInteractionController extends Controller
{
    public function assign(Request $request, User $client, CustomerActivityService $activities)
    {
        abort_unless($request->user('admin')?->hasRole(Admin::ROLE_OWNER, Admin::ROLE_SALES), 403);
        $data = $request->validate(['assigned_admin_id' => ['nullable', 'integer', $this->assigneeRule()]]);

        DB::transaction(function () use ($request, $client, $data, $activities) {
            $client = User::query()->lockForUpdate()->findOrFail($client->id);
            $previous = $client->assigned_admin_id;
            $assigned = isset($data['assigned_admin_id']) ? (int) $data['assigned_admin_id'] : null;
            if (($previous === null ? null : (int) $previous) === $assigned) {
                return;
            }
            $client->forceFill(['assigned_admin_id' => $assigned])->save();
            $this->record($activities, $request, $client, 'customer.assigned', 'Customer support assignment updated.', [
                'previous_admin_id' => $previous, 'assigned_admin_id' => $assigned,
            ]);
        });

        return redirect()->route('admin.clients.show', $client)->with('success', 'Customer assignment saved.');
    }

    public function storeNote(Request $request, User $client, CustomerActivityService $activities)
    {
        $this->authorizeOperator($request);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'channel' => ['required', Rule::in(['internal', 'whatsapp_manual'])],
        ]);
        $this->record($activities, $request, $client, $data['channel'] === 'internal' ? 'note.created' : 'whatsapp.manually_recorded', $data['message'], [
            'channel' => $data['channel'], 'recorded_manually' => true,
        ]);

        return redirect()->route('admin.clients.show', $client)->with('success', 'Note recorded internally. No message was sent to the customer.');
    }

    public function storeFollowUp(Request $request, User $client, CustomerActivityService $activities)
    {
        $this->authorizeOperator($request);
        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'due_at' => ['required', 'date'],
            'assigned_admin_id' => ['nullable', 'integer', $this->assigneeRule()],
        ]);
        if ($request->user('admin')->role === Admin::ROLE_SUPPORT) {
            abort_if(isset($data['assigned_admin_id']) && (int) $data['assigned_admin_id'] !== (int) $request->user('admin')->id, 403);
            $data['assigned_admin_id'] = $request->user('admin')->id;
        }
        DB::transaction(function () use ($request, $client, $data, $activities) {
            $followUp = CustomerFollowUp::create($data + [
                'user_id' => $client->id,
                'created_by_admin_id' => $request->user('admin')->id,
                'status' => 'pending',
            ]);
            $this->record($activities, $request, $client, 'follow_up.created', 'A follow-up task was created.', [
                'follow_up_id' => $followUp->id,
                'due_at' => $followUp->due_at->toIso8601String(),
                'assigned_admin_id' => $followUp->assigned_admin_id,
            ]);
        });

        return redirect()->route('admin.clients.show', $client)->with('success', 'Follow-up saved. This is an internal task, not an outgoing notification.');
    }

    public function completeFollowUp(Request $request, User $client, CustomerFollowUp $followUp, CustomerActivityService $activities)
    {
        $this->authorizeOperator($request);
        abort_unless((int) $followUp->user_id === (int) $client->id, 404);
        DB::transaction(function () use ($request, $client, $followUp, $activities) {
            $followUp = CustomerFollowUp::query()->lockForUpdate()->findOrFail($followUp->id);
            if ($request->user('admin')->role === Admin::ROLE_SUPPORT) {
                abort_if($followUp->assigned_admin_id !== null && (int) $followUp->assigned_admin_id !== (int) $request->user('admin')->id, 403);
            }
            if ($followUp->status === 'completed') {
                return;
            }
            $followUp->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
            $this->record($activities, $request, $client, 'follow_up.completed', 'A follow-up task was completed.', ['follow_up_id' => $followUp->id]);
        });

        return redirect()->route('admin.clients.show', $client)->with('success', 'Follow-up marked complete.');
    }

    public function replyTicket(Request $request, User $client, int $conversation, CustomerActivityService $activities)
    {
        $this->authorizeOperator($request);
        $data = $request->validate([
            'submission_id' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:5000', 'regex:/\S/u'],
        ]);
        DB::transaction(function () use ($request, $client, $conversation, $data, $activities) {
            $ticket = $this->portalTicket($client, $conversation)->lockForUpdate()->firstOrFail();
            $message = CustomerMessage::query()->createOrFirst([
                'conversation_id' => $ticket->id,
                'fingerprint' => hash('sha256', 'support-reply:'.$request->user('admin')->id.':'.$data['submission_id']),
            ], [
                'sender_type' => 'support',
                'sender_name' => Str::limit((string) $request->user('admin')->name, 191, ''),
                'content' => $data['message'], 'sent_at' => now(),
            ]);
            abort_unless($message->content === $data['message'], 409, 'This submission has already been used. Reload before sending a different reply.');
            if ($message->wasRecentlyCreated) {
                $ticket->forceFill(['status' => 'in_progress', 'ended_at' => null, 'updated_at' => now()])->save();
                $this->record($activities, $request, $client, 'support.team_replied', 'A reply was saved to the customer portal ticket.', ['conversation_id' => $ticket->id]);
            }
        });

        return redirect()->route('admin.clients.show', ['client' => $client, 'conversation_id' => $conversation])->withFragment('customer-conversations')
            ->with('success', 'Reply saved to the customer portal. No WhatsApp message or email was sent.');
    }

    public function updateTicketStatus(Request $request, User $client, int $conversation, CustomerActivityService $activities)
    {
        $this->authorizeOperator($request);
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])]]);
        DB::transaction(function () use ($request, $client, $conversation, $data, $activities) {
            $ticket = $this->portalTicket($client, $conversation)->lockForUpdate()->firstOrFail();
            if ($ticket->status === $data['status']) {
                return;
            }
            $ticket->forceFill(['status' => $data['status'], 'ended_at' => $data['status'] === 'resolved' ? now() : null])->save();
            $this->record($activities, $request, $client, 'support.status_updated', 'Customer portal support status updated.', [
                'conversation_id' => $ticket->id, 'status' => $ticket->status,
            ]);
        });

        return redirect()->route('admin.clients.show', ['client' => $client, 'conversation_id' => $conversation])->withFragment('customer-conversations')
            ->with('success', 'Customer portal ticket status updated.');
    }

    private function portalTicket(User $client, int $conversation)
    {
        return CustomerConversation::query()->whereKey($conversation)->where('user_id', $client->id)
            ->where('channel', 'portal')->where('property_id', 'customer-account');
    }

    private function authorizeOperator(Request $request): void
    {
        abort_unless($request->user('admin')?->hasRole(Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT), 403);
    }

    private function assigneeRule()
    {
        return Rule::exists('admins', 'id')->where(fn ($query) => $query->whereIn('role', [Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT]));
    }

    private function record(CustomerActivityService $activities, Request $request, User $client, string $type, string $message, array $details): void
    {
        $activities->record([
            'source' => 'crm', 'event_id' => (string) Str::uuid(), 'event_type' => $type,
            'entity_type' => 'user', 'entity_id' => (string) $client->id,
            'user_id' => $client->id, 'admin_id' => $request->user('admin')->id,
            'message' => $message, 'details' => $details, 'occurred_at' => now(),
        ]);
    }
}
