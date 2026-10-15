<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Package;
use App\Models\TrialClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerTrialPanelTest extends TestCase
{
    use RefreshDatabase;

    public function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_a_lead_click_is_not_an_activated_trial(): void
    {
        $trial = $this->trial();

        $this->assertSame('pending', $trial->trial_status);
        $this->assertNull($trial->trial_started_at);
        $this->assertNull($trial->trial_expires_at);
        $this->assertNull($trial->trial_owner_verified_at);
    }

    public function test_confirmed_admin_activation_records_real_dates_and_exact_account_owner(): void
    {
        $user = User::factory()->create(['email' => 'trial-owner@example.test']);
        $trial = $this->trial();
        $admin = $this->signInAdmin();
        $start = now()->subHour()->startOfMinute();
        $end = now()->addHours(11)->startOfMinute();

        $this->from(route('admin.trial_clicks.index'))
            ->patch(route('admin.trial_clicks.trial', $trial), [
                'confirmed_account_email' => 'TRIAL-OWNER@example.test',
                'owner_confirmed' => '1',
                'trial_started_at' => $start->format('Y-m-d\TH:i'),
                'trial_expires_at' => $end->format('Y-m-d\TH:i'),
            ])->assertRedirect(route('admin.trial_clicks.index'))->assertSessionHasNoErrors();

        $trial->refresh();
        $this->assertSame($user->id, $trial->user_id);
        $this->assertSame($admin->id, $trial->trial_activated_by_admin_id);
        $this->assertNotNull($trial->trial_owner_verified_at);
        $this->assertTrue($trial->trial_started_at->equalTo($start));
        $this->assertTrue($trial->trial_expires_at->equalTo($end));
        $this->assertSame('active', $trial->trial_status);
        $this->assertSame('new', $trial->status);
        $this->assertNull($trial->trial_package_id);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_phone_match_cannot_substitute_for_confirmed_account_email(): void
    {
        $user = User::factory()->create(['phone' => '+923001234567']);
        $trial = $this->trial(['phone_normalized' => $user->phone_normalized]);
        $this->signInAdmin();

        $this->patch(route('admin.trial_clicks.trial', $trial), [
            'confirmed_account_email' => 'unknown@example.test',
            'owner_confirmed' => '1',
            'trial_started_at' => now()->format('Y-m-d\TH:i'),
            'trial_expires_at' => now()->addHours(12)->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('confirmed_account_email');

        $trial->refresh();
        $this->assertNull($trial->user_id);
        $this->assertNull($trial->trial_owner_verified_at);
        $this->assertNull($trial->trial_started_at);
    }

    public function test_activation_requires_confirmation_and_increasing_dates(): void
    {
        $user = User::factory()->create();
        $trial = $this->trial();
        $this->signInAdmin();

        $this->patch(route('admin.trial_clicks.trial', $trial), [
            'confirmed_account_email' => $user->email,
            'trial_started_at' => now()->format('Y-m-d\TH:i'),
            'trial_expires_at' => now()->subHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors(['owner_confirmed', 'trial_expires_at']);

        $this->assertNull($trial->fresh()->trial_started_at);
    }

    public function test_ambiguous_normalized_emails_do_not_assign_trial_ownership(): void
    {
        User::factory()->create(['email' => 'trial-duplicate@example.test']);
        User::factory()->create(['email' => ' trial-duplicate@example.test ']);
        $trial = $this->trial();
        $this->signInAdmin();

        $this->patch(route('admin.trial_clicks.trial', $trial), [
            'confirmed_account_email' => 'trial-duplicate@example.test',
            'owner_confirmed' => '1',
            'trial_started_at' => now()->format('Y-m-d\TH:i'),
            'trial_expires_at' => now()->addHours(12)->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('confirmed_account_email');

        $this->assertNull($trial->fresh()->user_id);
        $this->assertNull($trial->fresh()->trial_owner_verified_at);
    }

    public function test_non_admin_cannot_activate_a_trial(): void
    {
        $trial = $this->trial();
        $this->patch(route('admin.trial_clicks.trial', $trial), [])
            ->assertRedirect(route('admin.login'));

        $this->assertNull($trial->fresh()->trial_started_at);
    }

    public function test_contact_lead_cannot_be_accidentally_activated_as_a_trial(): void
    {
        $lead = $this->trial(['intent' => 'contact']);
        $this->signInAdmin();
        $this->patch(route('admin.trial_clicks.trial', $lead), [])->assertUnprocessable();
        $this->assertNull($lead->fresh()->trial_started_at);
    }

    public function test_admin_lead_screen_renders_separate_trial_form_only_for_trial_requests(): void
    {
        $trial = $this->trial();
        $contact = $this->trial(['intent' => 'contact']);
        $this->signInAdmin();

        $this->get(route('admin.trial_clicks.index'))->assertOk()
            ->assertSee('id="trialDates'.$trial->id.'"', false)
            ->assertSee('form="trialDates'.$trial->id.'"', false)
            ->assertDontSee('id="trialDates'.$contact->id.'"', false)
            ->assertSee('Confirmed customer account email');
    }

    public function test_trial_status_uses_its_actual_start_and_expiry(): void
    {
        $trial = $this->trial([
            'trial_started_at' => now()->addHour(),
            'trial_expires_at' => now()->addHours(13),
        ]);
        $this->assertSame('scheduled', $trial->trial_status);

        $this->travel(2)->hours();
        $this->assertSame('active', $trial->trial_status);
        $this->travel(12)->hours();
        $this->assertSame('expired', $trial->trial_status);
        $this->travelBack();
    }

    public function test_customer_panel_shows_only_owned_and_verified_trials(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $visible = $this->trial([
            'user_id' => $customer->id, 'trial_owner_verified_at' => now(), 'package_name' => 'My verified trial',
        ]);
        $this->trial(['user_id' => $customer->id, 'package_name' => 'Unverified trial must stay private']);
        $this->trial([
            'user_id' => $other->id, 'trial_owner_verified_at' => now(), 'package_name' => 'Another account trial',
        ]);

        $this->actingAs($customer, 'web')->withSession([
            'customer_verified_email' => hash('sha256', User::normalizeEmail($customer->email)),
        ])->get(route('customer.account'))->assertOk()
            ->assertSee('My verified trial')->assertSee($visible->lead_code)
            ->assertSee('Not activated yet')
            ->assertDontSee('Unverified trial must stay private')->assertDontSee('Another account trial');
    }

    public function test_authenticated_trial_request_is_recorded_once_without_starting_payment_or_dates(): void
    {
        $customer = User::factory()->create();
        $package = Package::create([
            'type' => 'iptv', 'vendor' => 'opplex', 'title' => 'Opplex',
            'price_amount' => 10, 'duration_months' => 1,
            'free_trial_hours' => 12, 'is_available' => true, 'active' => true,
        ]);
        $payload = ['package_id' => $package->id, 'submission_id' => (string) Str::uuid()];
        $this->actingAs($customer, 'web')->withSession([
            'customer_verified_email' => hash('sha256', User::normalizeEmail($customer->email)),
        ]);

        $this->post(route('customer.trials.request'), $payload)->assertRedirect(route('customer.account'));
        $this->post(route('customer.trials.request'), $payload)->assertRedirect(route('customer.account'));

        $this->assertDatabaseCount('trial_clicks', 1);
        $trial = TrialClick::firstOrFail();
        $this->assertSame($customer->id, $trial->user_id);
        $this->assertSame($package->id, $trial->trial_package_id);
        $this->assertNotNull($trial->trial_owner_verified_at);
        $this->assertNull($trial->trial_started_at);
        $this->assertNull($trial->trial_expires_at);
        $this->assertSame('pending', $trial->trial_status);
        $this->assertDatabaseCount('orders', 0);
    }

    private function trial(array $attributes = []): TrialClick
    {
        return TrialClick::create(array_merge([
            'event_id' => (string) Str::uuid(),
            'intent' => 'trial',
            'status' => 'new',
        ], $attributes));
    }

    private function signInAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Trial Test Admin',
            'email' => 'trial-admin@example.test',
            'password' => bcrypt('isolated-test-only'),
            'role' => Admin::ROLE_OWNER,
            'must_change_password' => false,
            'auth_version' => 1,
        ]);
        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin, 'admin')->withSession([
            'admin_auth_version' => 1,
            'admin_two_factor_verified_id' => $admin->id,
        ]);

        return $admin;
    }
}
