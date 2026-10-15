<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Models\CustomerConversation;
use App\Models\CustomerMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerSupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
    }

    public function test_support_requires_a_verified_customer_session(): void
    {
        $this->get('/account/support')->assertRedirect();
        $this->post('/account/support', $this->submission())->assertRedirect();
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->get('/account/support')->assertRedirect();
        $this->assertDatabaseCount('customer_conversations', 0);
    }

    public function test_ticket_creation_is_owned_server_generated_and_idempotent(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $data = $this->submission('Please help with my setup.') + [
            'user_id' => $other->id, 'channel' => 'tawk', 'property_id' => 'foreign',
            'external_id' => 'client-supplied-id', 'status' => 'resolved',
        ];
        $this->customer($user)->post('/account/support', $data)->assertRedirect();
        $ticket = CustomerConversation::query()->sole();
        $this->assertSame((int) $user->id, (int) $ticket->user_id);
        $this->assertSame('portal', $ticket->channel);
        $this->assertSame('customer-account', $ticket->property_id);
        $this->assertSame('open', $ticket->status);
        $this->assertTrue(Str::isUuid($ticket->external_id));
        $this->assertNotSame($data['submission_id'], $ticket->external_id);
        $this->post('/account/support', $data)->assertRedirect(route('customer.support.show', $ticket->id));
        $this->assertDatabaseCount('customer_conversations', 1);
        $this->assertDatabaseCount('customer_messages', 1);
        $this->assertSame(1, CustomerActivity::where('event_type', 'support.ticket_created')->count());
        Mail::assertNothingOutgoing();
        Notification::assertNothingSent();
    }

    public function test_reusing_a_ticket_submission_for_different_content_is_rejected(): void
    {
        $data = $this->submission();
        $this->customer(User::factory()->create())->post('/account/support', $data)->assertRedirect();
        $this->post('/account/support', array_replace($data, ['message' => 'Different message']))->assertStatus(409);
        $this->assertDatabaseCount('customer_conversations', 1);
        $this->assertDatabaseCount('customer_messages', 1);
    }

    public function test_customer_only_sees_own_portal_tickets_and_public_messages(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $own = $this->ticket($user);
        $foreign = $this->ticket($other);
        $tawk = $this->ticket($user, ['channel' => 'tawk']);
        $legacy = $this->ticket($user, ['property_id' => 'old-portal']);
        $this->message($own, 'customer', '<script>alert("x")</script> My question');
        $this->message($own, 'support', 'Public support answer');
        $this->message($own, 'internal', 'Private staff discussion');
        $this->message($tawk, 'customer', 'Old Tawk transcript');

        $this->customer($user)->get('/account/support')
            ->assertOk()->assertSee('supportTickets');
        $this->getJson('/api/browser/account/support')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $own->id);
        $page = $this->get('/account/support/'.$own->id)->assertOk()
            ->assertSee('supportMessages')->assertSee('text = api.escape', false)
            ->assertSee("'<':'&lt;','>':'&gt;'", false)->assertSee('text(message.content)', false)
            ->assertDontSee('<script>alert("x")</script>', false)
            ->assertDontSee('Private staff discussion')->assertDontSee('Old Tawk transcript');
        $this->assertStringContainsString('no-store', $page->headers->get('Cache-Control'));
        $this->getJson('/api/browser/account/support/'.$own->id)->assertOk()->assertJsonPath('data.id', $own->id);
        $response = $this->getJson('/api/browser/account/support/'.$own->id.'/messages')->assertOk()
            ->assertHeader('Content-Type', 'application/json')->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.content', '<script>alert("x")</script> My question')
            ->assertJsonPath('data.1.content', 'Public support answer')
            ->assertJsonMissing(['content' => 'Private staff discussion'])
            ->assertJsonMissing(['content' => 'Old Tawk transcript']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        foreach ([$foreign, $tawk, $legacy] as $privateTicket) {
            $this->get('/account/support/'.$privateTicket->id)->assertNotFound();
            $this->post('/account/support/'.$privateTicket->id.'/messages', $this->submission())->assertNotFound();
            $this->getJson('/api/browser/account/support/'.$privateTicket->id)->assertNotFound();
            $this->getJson('/api/browser/account/support/'.$privateTicket->id.'/messages')->assertNotFound();
            $this->postJson('/api/browser/account/support/'.$privateTicket->id.'/messages', $this->submission())->assertNotFound();
        }
    }

    public function test_customer_reply_is_idempotent_and_reopens_resolved_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user, ['status' => 'resolved', 'ended_at' => now()]);
        $data = $this->submission('I still need help.');
        $this->customer($user)->post('/account/support/'.$ticket->id.'/messages', $data)->assertRedirect();
        $this->assertSame('open', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->ended_at);
        $ticket->forceFill(['status' => 'in_progress'])->save();
        $this->post('/account/support/'.$ticket->id.'/messages', $data)->assertRedirect();
        $this->assertSame('in_progress', $ticket->fresh()->status);
        $this->post('/account/support/'.$ticket->id.'/messages', array_replace($data, ['message' => 'Altered']))->assertStatus(409);
        $this->assertDatabaseCount('customer_messages', 1);
        $this->assertSame(1, CustomerActivity::where('event_type', 'support.customer_replied')->count());
    }

    public function test_messages_require_a_uuid_and_bounded_non_empty_text(): void
    {
        $this->customer(User::factory()->create());
        $this->postJson('/account/support', ['submission_id' => 'invalid', 'message' => 'Hello'])->assertUnprocessable()->assertJsonValidationErrors('submission_id');
        $this->postJson('/account/support', $this->submission(str_repeat('a', 5001)))->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->postJson('/account/support', $this->submission('   '))->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->assertDatabaseCount('customer_conversations', 0);
    }

    public function test_long_threads_open_at_latest_messages_and_keep_history_available(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user);
        for ($number = 1; $number <= 31; $number++) {
            $this->message($ticket, 'customer', 'Thread message '.$number.'.');
        }
        $this->customer($user)->get('/account/support/'.$ticket->id)->assertOk()->assertSee('supportMessages');
        $url = '/api/browser/account/support/'.$ticket->id.'/messages';
        $this->getJson($url)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.content', 'Thread message 31.')
            ->assertJsonMissing(['content' => 'Thread message 1.'])
            ->assertJsonPath('meta.current_page', 2)->assertJsonPath('meta.last_page', 2)->assertJsonPath('meta.total', 31);
        $this->get('/account/support/'.$ticket->id.'?messages_page=1')->assertOk()
            ->assertSee("messages(Number(params.get('messages_page')) || undefined)", false);
        $this->getJson($url.'?page=1')->assertOk()->assertJsonCount(30, 'data')
            ->assertJsonPath('data.0.content', 'Thread message 1.')
            ->assertJsonMissing(['content' => 'Thread message 31.'])
            ->assertJsonPath('meta.current_page', 1)->assertJsonPath('meta.last_page', 2)->assertJsonPath('meta.total', 31);
    }

    public function test_owner_sales_and_support_can_reply_and_resolve_portal_tickets(): void
    {
        $user = User::factory()->create();
        foreach ([Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT] as $role) {
            $ticket = $this->ticket($user);
            $admin = $this->admin($role);
            $data = $this->submission('We are checking your request.');
            $replyUrl = route('admin.clients.tickets.reply', ['client' => $user->id, 'conversation' => $ticket->id]);
            $statusUrl = route('admin.clients.tickets.status', ['client' => $user->id, 'conversation' => $ticket->id]);
            $this->administrator($admin)->post($replyUrl, $data)->assertRedirect();
            $this->post($replyUrl, $data)->assertRedirect();
            $this->assertSame(1, $ticket->messages()->count());
            $this->assertSame('support', $ticket->messages()->sole()->sender_type);
            $this->assertSame('in_progress', $ticket->fresh()->status);
            $this->patch($statusUrl, ['status' => 'resolved'])->assertRedirect();
            $this->assertSame('resolved', $ticket->fresh()->status);
            $this->assertNotNull($ticket->fresh()->ended_at);
            $this->patchJson($statusUrl, ['status' => 'paid'])->assertUnprocessable();
        }
        $this->assertSame(3, CustomerActivity::where('event_type', 'support.team_replied')->count());
        Mail::assertNothingOutgoing();
        Notification::assertNothingSent();
    }

    public function test_content_role_cannot_reply_or_change_ticket_status(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user);
        $params = ['client' => $user->id, 'conversation' => $ticket->id];
        $this->administrator($this->admin(Admin::ROLE_CONTENT))
            ->post(route('admin.clients.tickets.reply', $params), $this->submission())->assertForbidden();
        $this->patch(route('admin.clients.tickets.status', $params), ['status' => 'resolved'])->assertForbidden();
        $this->assertSame('open', $ticket->fresh()->status);
        $this->assertDatabaseCount('customer_messages', 0);
    }

    public function test_admin_cannot_reply_to_another_clients_or_tawk_conversation(): void
    {
        $user = User::factory()->create();
        $foreign = $this->ticket(User::factory()->create());
        $tawk = $this->ticket($user, ['channel' => 'tawk']);
        $legacy = $this->ticket($user, ['property_id' => 'old-portal']);
        $this->administrator($this->admin(Admin::ROLE_OWNER));
        foreach ([$foreign, $tawk, $legacy] as $ticket) {
            $params = ['client' => $user->id, 'conversation' => $ticket->id];
            $this->post(route('admin.clients.tickets.reply', $params), $this->submission())->assertNotFound();
            $this->patch(route('admin.clients.tickets.status', $params), ['status' => 'resolved'])->assertNotFound();
        }
        $this->assertDatabaseCount('customer_messages', 0);
    }

    public function test_admin_reply_form_appears_only_for_customer_portal_conversations(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user);
        $tawk = $this->ticket($user, ['channel' => 'tawk']);
        $this->administrator($this->admin(Admin::ROLE_SUPPORT));
        $this->get(route('admin.clients.show', ['client' => $user->id, 'conversation_id' => $ticket->id]))
            ->assertOk()->assertSee('Save customer-visible reply');
        $this->get(route('admin.clients.show', ['client' => $user->id, 'conversation_id' => $tawk->id]))
            ->assertOk()->assertDontSee('Save customer-visible reply');
    }

    private function customer(User $user): static
    {
        return $this->actingAs($user, 'web')->withSession([
            'customer_verified_email' => hash('sha256', User::normalizeEmail($user->email)),
        ]);
    }

    private function administrator(Admin $admin): static
    {
        return $this->actingAs($admin, 'admin')->withSession([
            'admin_auth_version' => $admin->auth_version,
            'admin_two_factor_verified_id' => $admin->id,
        ]);
    }

    private function admin(string $role): Admin
    {
        return Admin::query()->forceCreate([
            'name' => 'Test support', 'email' => Str::uuid().'@example.test',
            'password' => bcrypt('test-password'), 'role' => $role,
            'auth_version' => 1, 'must_change_password' => false, 'two_factor_confirmed_at' => now(),
        ]);
    }

    private function ticket(User $user, array $attributes = []): CustomerConversation
    {
        return CustomerConversation::create(array_replace([
            'user_id' => $user->id, 'channel' => 'portal', 'property_id' => 'customer-account',
            'external_id' => (string) Str::uuid(), 'status' => 'open', 'started_at' => now(),
        ], $attributes));
    }

    private function message(CustomerConversation $ticket, string $sender, string $content): CustomerMessage
    {
        return $ticket->messages()->create([
            'fingerprint' => hash('sha256', (string) Str::uuid()), 'sender_type' => $sender,
            'content' => $content, 'sent_at' => now(),
        ]);
    }

    private function submission(string $message = 'Please help with this request.'): array
    {
        return ['submission_id' => (string) Str::uuid(), 'message' => $message];
    }
}
