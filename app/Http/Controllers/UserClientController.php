<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Clients\{
    StoreClientRequest,
    UpdateClientRequest,
    ImportClientsRequest
};
use App\Models\User;
use App\Models\Admin;
use App\Models\CustomerActivity;
use App\Services\Clients\CustomerIdentityService;
use App\Services\Clients\{
    ClientQueryService,
    ClientCrudService,
    ClientImportService
};
use App\Services\MarketingWorkflowService;
use App\Traits\HelperFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Nakanakaii\Countries\Countries;

class UserClientController extends Controller
{
    use HelperFunction;

    public function __construct(
        private ClientQueryService $query,
        private ClientCrudService $crud,
        private ClientImportService $importer,
    ) {}

    public function exportFacebookCsv(Request $request)
    {
        $filename = 'facebook_custom_audience_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');

            // Excel UTF-8 BOM (optional but helpful)
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV header (exact FB order)
            fputcsv($out, [
                'email',
                'email',
                'email',
                'phone',
                'phone',
                'phone',
                'madid',
                'fn',
                'ln',
                'zip',
                'ct',
                'st',
                'country',
                'dob',
                'doby',
                'gen',
                'age',
                'uid',
                'value',
            ]);

            \App\Models\User::query()
                ->whereNotNull('marketing_ads_consented_at')
                ->where(function ($query) {
                    $query->whereNull('marketing_ads_opted_out_at')
                        ->orWhereColumn('marketing_ads_consented_at', '>', 'marketing_ads_opted_out_at');
                })
                ->orderBy('id')
                ->chunk(500, function ($users) use ($out) {
                foreach ($users as $u) {
                    [$fn, $ln] = $this->splitName((string) $u->name);
                    $email     = strtolower(trim((string) $u->email));
                    $phone     = $this->formatPhoneForCsv((string) $u->phone); // <- Excel-safe + FB-safe
                    $country   = strtoupper((string) ($u->country ?? ''));
                    $uid       = 'UID-' . (string) $u->id; // <- Excel me E+ se bachega

                    fputcsv($out, [
                        $email,
                        '',
                        '',
                        $phone,
                        '',
                        '',
                        '',
                        $fn,
                        $ln,
                        '',
                        '',
                        '',
                        $country,
                        '',
                        '',
                        '',
                        '',
                        $uid,
                        '',
                    ]);
                }
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                @flush();
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Name ko First/Last me todna */
    private function splitName(string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') return ['', ''];
        $parts = explode(' ', $name, 2);
        return [$parts[0], $parts[1] ?? ''];
    }

    /**
     * Excel ko scientific notation se rokne ke liye
     * phone ko groups me space ke sath likhte hain.
     * FB spaces/separators ko ignore kar deta hai.
     */
    private function formatPhoneForCsv(string $raw): string
    {
        // sirf digits aur leading +
        $raw = preg_replace('/[^0-9+]/', '', $raw);
        if ($raw === '') return '';

        if ($raw[0] !== '+') {
            // agar + nahi diya to add kar dein (E.164 looking)
            $raw = '+' . $raw;
        }

        $digits = ltrim($raw, '+');

        // right se group: last 4, baqi 3-3 (generic, country-agnostic)
        if (strlen($digits) <= 4) {
            return '+' . $digits;
        }

        $parts = [];
        $parts[] = substr($digits, -4);              // last 4
        $digits  = substr($digits, 0, -4);

        while (strlen($digits) > 0) {
            $parts[] = substr($digits, -3);
            $digits  = substr($digits, 0, -3);
        }

        $parts = array_reverse($parts);
        return '+' . implode(' ', $parts);           // e.g. +923 331 234 5678
    }


    public function index(Request $request, MarketingWorkflowService $workflows)
    {
        $this->validateCrmFilters($request);
        $clients = $this->runIndex($this->query, $request);
        $contactUpdateEligible = $workflows->contactNumberUpdateAudienceCount();
        $unlinkedActivity = $this->query->unlinkedActivityCounts();

        $admins = Admin::query()->whereIn('role', [Admin::ROLE_OWNER, Admin::ROLE_SALES, Admin::ROLE_SUPPORT])->orderBy('name')->get(['id', 'name']);
        $activitySources = CustomerActivity::query()->distinct()->orderBy('source')->pluck('source');

        return view('admin.clients.index', compact('clients', 'contactUpdateEligible', 'unlinkedActivity', 'admins', 'activitySources'));
    }

    public function exportCrm(Request $request)
    {
        abort_unless($request->user('admin')?->hasRole(Admin::ROLE_OWNER, Admin::ROLE_SALES), 403);
        $this->validateCrmFilters($request);
        $clients = User::query();
        $this->query->applyFilters($clients, $request);
        $rows = DB::table('users')->whereIn('users.id', $clients->select('users.id'))
            ->leftJoin('orders', 'orders.user_id', '=', 'users.id')
            ->select([
                'users.id as customer_id', 'users.name', 'users.email', 'users.phone', 'users.country',
                'users.assigned_admin_id', 'orders.id as order_id', 'orders.package', 'orders.duration',
                'orders.buying_date', 'orders.expiry_date', 'orders.status as fulfillment_status',
                'orders.payment_status', 'orders.paid_currency', 'orders.paid_amount',
            ])->orderBy('users.id')->orderBy('orders.id');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Customer ID', 'Name', 'Email', 'Phone', 'Country', 'Assigned admin ID', 'Order ID', 'Package', 'Duration (stored value)', 'Start date', 'End date', 'Fulfillment status', 'Payment status', 'Paid currency', 'Paid amount'], ',', '"', '');
            foreach ($rows->cursor() as $row) {
                $cells = array_map(function ($value) {
                    $value = (string) ($value ?? '');
                    return preg_match('/^[\s\x00-\x1F]*[=+@-]/u', $value) ? "'" . $value : $value;
                }, array_values((array) $row));
                fputcsv($out, $cells, ',', '"', '');
            }
            fclose($out);
        }, 'customer360-' . now()->format('Y-m-d-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function unlinked(Request $request, CustomerIdentityService $identity)
    {
        abort_unless($request->user('admin')?->isOwner(), 403);
        $data = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(CustomerIdentityService::REVIEW_TYPES))],
            'conversation_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $type = $data['type'] ?? 'trial_click';
        $records = $identity->unlinkedQuery($type)->orderBy('id')->paginate(20)->withQueryString();
        $previews = $records->getCollection()->map(fn ($record) => $identity->previewRecord($type, $record));
        $selectedConversation = null;
        $unlinkedMessages = null;
        if ($request->filled('conversation_id')) {
            abort_unless($type === 'conversation', 422);
            $selectedConversation = $identity->unlinkedQuery('conversation')->findOrFail($data['conversation_id']);
            $unlinkedMessages = $selectedConversation->messages()->orderBy('sent_at')->orderBy('id')
                ->paginate(30, ['*'], 'messages_page')->withQueryString();
        }

        return response()->view('admin.clients.unlinked', compact('type', 'records', 'previews', 'selectedConversation', 'unlinkedMessages'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function linkActivity(Request $request, CustomerIdentityService $identity)
    {
        abort_unless($request->user('admin')?->isOwner(), 403);
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(CustomerIdentityService::REVIEW_TYPES))],
            'record_id' => ['required', 'integer', 'min:1'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'fingerprint' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'verified' => ['required', 'accepted'],
        ]);
        $identity->linkReviewed($data['type'], (int) $data['record_id'], (int) $data['user_id'], $data['fingerprint'], (int) $request->user('admin')->id);

        return redirect()->route('admin.clients.unlinked', ['type' => $data['type']])->with('success', 'Only the selected verified record was linked. Original contact details, payment and subscription dates were not changed.');
    }

    private function validateCrmFilters(Request $request): void
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'assigned_admin_id' => ['nullable', 'regex:/^(unassigned|[1-9][0-9]*)$/'],
            'subscription' => ['nullable', Rule::in(['active', 'expired', 'expiring'])],
            'payment' => ['nullable', Rule::in(['paid', 'unpaid', 'test_paid'])],
            'follow_up' => ['nullable', Rule::in(['pending', 'overdue'])],
            'source' => ['nullable', 'string', 'max:32'],
        ]);
    }

    public function scheduleContactNumberUpdate(Request $request, MarketingWorkflowService $workflows)
    {
        $request->validate(['confirm' => ['required', 'accepted']]);

        if ((string) config('services.whatsapp.number') === '') {
            return back()->with('error', 'Set WHATSAPP_NUMBER before scheduling this customer update.');
        }

        if (config('mail.default') === 'log') {
            return back()->with('error', 'Configure a real email provider before scheduling customer emails.');
        }

        $result = $workflows->scheduleContactNumberUpdate();

        return back()->with(
            'success',
            "{$result['scheduled']} customer email(s) scheduled. {$result['retried']} failed email(s) rescheduled. {$result['alreadyScheduled']} already scheduled or sent for this number."
        );
    }

    public function create()
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.create', compact('countries'));
    }

    public function show(Request $request, User $client)
    {
        $request->validate([
            'activity_source' => ['nullable', 'string', 'max:32'],
            'conversation_id' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->view('admin.clients.show', [
            'client' => $client,
            ...$this->query->profile($client, $request),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function store(StoreClientRequest $request)
    {
        $this->crud->create($request->validated());
        return redirect()->route('admin.clients.index')->with('success', __('interface.admin.flash.client_created'));
    }

    public function edit(User $client)
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.edit', compact('client', 'countries'));
    }

    public function update(UpdateClientRequest $request, User $client)
    {
        $this->crud->update($request->validated(), $client);
        return redirect()->route('admin.clients.index')->with('success', __('interface.admin.flash.client_updated'));
    }

    public function destroy(User $client)
    {
        $this->crud->delete($client);
        return back()->with('success', __('interface.admin.flash.client_deleted'));
    }

    public function import(ImportClientsRequest $request)
    {
        @set_time_limit(800);
        $count = $this->importer->importFromCsv($request->file('csv_file'));
        return redirect()->route('admin.clients.index')->with('success', "{$count} clients imported successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('client_ids', []);
        if (empty($ids)) {
            return back()->with('error', __('interface.admin.flash.no_clients_selected'));
        }

        $deleted = $this->crud->bulkDelete($ids);
        return back()->with('success', "{$deleted} client(s) deleted successfully.");
    }
}
