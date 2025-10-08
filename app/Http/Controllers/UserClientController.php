<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Clients\{
    StoreClientRequest,
    UpdateClientRequest,
    ImportClientsRequest
};
use App\Models\User;
use App\Services\Clients\{
    ClientQueryService,
    ClientCrudService,
    ClientImportService
};
use App\Traits\HelperFunction;
use Illuminate\Http\Request;
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

            // Export ALL clients
            \App\Models\User::orderBy('id')->chunk(500, function ($users) use ($out) {
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


    public function index(Request $request)
    {
        $clients = $this->runIndex($this->query, $request);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.create', compact('countries'));
    }

    public function store(StoreClientRequest $request)
    {
        $this->crud->create($request->validated());
        return redirect()->route('admin.clients.index')->with('success', __('messages.client_created'));
    }

    public function edit(User $client)
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.edit', compact('client', 'countries'));
    }

    public function update(UpdateClientRequest $request, User $client)
    {
        $this->crud->update($request->validated(), $client);
        return redirect()->route('admin.clients.index')->with('success', __('messages.client_updated'));
    }

    public function destroy(User $client)
    {
        $this->crud->delete($client);
        return back()->with('success', __('messages.client_deleted'));
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
            return back()->with('error', __('messages.no_clients_selected'));
        }

        $deleted = $this->crud->bulkDelete($ids);
        return back()->with('success', "{$deleted} client(s) deleted successfully.");
    }
}
