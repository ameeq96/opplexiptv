<?php

namespace App\Http\Controllers;

use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\User;
use App\Services\Clients\CustomerActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerSupportController extends Controller
{
    public function index(Request $request)
    {
        return $this->ticketView($request);
    }

    public function store(Request $request)
    {
        $data = $this->messageData($request);
        $userId = (int) $request->user('web')->id;
        $fingerprint = hash('sha256', 'customer-ticket:'.$userId.':'.$data['submission_id']);

        $ticket = DB::transaction(function () use ($request, $data, $userId, $fingerprint) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $existing = $this->tickets($userId)->whereHas('messages', fn ($query) => $query->where('fingerprint', $fingerprint))->first();
            if ($existing) {
                abort_unless($existing->messages()->where('fingerprint', $fingerprint)->value('content') === $data['message'], 409, 'This submission has already been used. Please reload before sending a different message.');
                return $existing;
            }

            $ticket = CustomerConversation::create([
                'channel' => 'portal', 'property_id' => 'customer-account',
                'external_id' => (string) Str::uuid(), 'user_id' => $userId,
                'visitor_name' => $user->name, 'visitor_email' => $user->email,
                'visitor_phone' => $user->phone, 'status' => 'open', 'started_at' => now(),
            ]);
            $ticket->messages()->create([
                'fingerprint' => $fingerprint, 'sender_type' => 'customer',
                'sender_name' => Str::limit((string) $user->name, 191, ''),
                'content' => $data['message'], 'sent_at' => now(),
            ]);
            $this->record($request, $ticket, 'support.ticket_created', $fingerprint);

            return $ticket;
        });

        return redirect()->route('customer.support.show', $ticket->id)->with('success', 'Your support request has been saved. Check this page for replies from our team.');
    }

    public function show(Request $request, int $conversation)
    {
        return $this->ticketView($request, $this->tickets((int) $request->user('web')->id)->findOrFail($conversation));
    }

    public function reply(Request $request, int $conversation)
    {
        $data = $this->messageData($request);
        $userId = (int) $request->user('web')->id;
        DB::transaction(function () use ($request, $data, $userId, $conversation) {
            $ticket = $this->tickets($userId)->lockForUpdate()->findOrFail($conversation);
            $fingerprint = hash('sha256', 'customer-reply:'.$userId.':'.$data['submission_id']);
            $message = CustomerMessage::query()->createOrFirst([
                'conversation_id' => $ticket->id, 'fingerprint' => $fingerprint,
            ], [
                'sender_type' => 'customer',
                'sender_name' => Str::limit((string) $request->user('web')->name, 191, ''),
                'content' => $data['message'], 'sent_at' => now(),
            ]);
            abort_unless($message->content === $data['message'], 409, 'This submission has already been used. Please reload before sending a different message.');
            if ($message->wasRecentlyCreated) {
                $ticket->forceFill(['status' => 'open', 'ended_at' => null, 'updated_at' => now()])->save();
                $this->record($request, $ticket, 'support.customer_replied', $fingerprint);
            }
        });

        return redirect()->route('customer.support.show', $conversation)->with('success', 'Your reply has been saved. Our team can read it in your support ticket.');
    }

    private function tickets(int $userId)
    {
        return CustomerConversation::query()->where('user_id', $userId)
            ->where('channel', 'portal')->where('property_id', 'customer-account');
    }

    private function ticketView(Request $request, ?CustomerConversation $ticket = null)
    {
        $request->validate([
            'tickets_page' => ['nullable', 'integer', 'min:1'],
            'messages_page' => ['nullable', 'integer', 'min:1'],
        ]);
        $tickets = $this->tickets((int) $request->user('web')->id)->latest('updated_at')->orderByDesc('id')
            ->paginate(15, ['*'], 'tickets_page')->withQueryString();
        $messages = null;
        if ($ticket) {
            $publicMessages = $ticket->messages()->whereIn('sender_type', ['customer', 'support']);
            $lastPage = max(1, (int) ceil($publicMessages->count() / 30));
            $page = min((int) ($request->query('messages_page') ?: $lastPage), $lastPage);
            $messages = $publicMessages->orderBy('sent_at')->orderBy('id')
                ->paginate(30, ['*'], 'messages_page', $page)->withQueryString();
        }

        return response()->view('pages.account.support', compact('tickets', 'ticket', 'messages'))
            ->header('Cache-Control', 'private, no-store');
    }

    private function messageData(Request $request): array
    {
        return $request->validate([
            'submission_id' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:5000', 'regex:/\S/u'],
        ]);
    }

    private function record(Request $request, CustomerConversation $ticket, string $type, string $fingerprint): void
    {
        app(CustomerActivityService::class)->record([
            'source' => 'customer_portal', 'event_id' => $fingerprint, 'event_type' => $type,
            'entity_type' => 'conversation', 'entity_id' => (string) $ticket->id,
            'user_id' => $request->user('web')->id, 'message' => 'Customer portal support ticket updated.',
            'details' => ['conversation_id' => $ticket->id, 'status' => $ticket->status],
            'occurred_at' => now(),
        ]);
    }
}
