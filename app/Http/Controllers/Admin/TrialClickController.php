<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrialClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrialClickController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10,20,30,40,100], true)) $perPage = 10;

        $q = TrialClick::query();
        $activityAt = DB::raw('COALESCE(whatsapp_contact_consented_at, created_at)');

        if (in_array($request->input('status'), TrialClick::STATUSES, true)) {
            $q->where('status', $request->input('status'));
        }

        // Search
        if ($search = trim((string) $request->input('search'))) {
            $eventSearch = preg_replace('/^OPX-/i', '', $search);
            $q->where(function ($w) use ($search, $eventSearch) {
                $w->where('event_id', 'like', "%$eventSearch%")
                  ->orWhere('contact_name', 'like', "%$search%")
                  ->orWhere('phone_normalized', 'like', "%$search%")
                  ->orWhere('page', 'like', "%$search%")
                  ->orWhere('destination', 'like', "%$search%")
                  ->orWhere('fbp', 'like', "%$search%")
                  ->orWhere('fbc', 'like', "%$search%")
                  ->orWhere('utm_campaign', 'like', "%$search%")
                  ->orWhere('intent', 'like', "%$search%")
                  ->orWhere('placement', 'like', "%$search%")
                  ->orWhere('package_name', 'like', "%$search%")
                  ->orWhere('vendor', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%")
                  ->orWhere('ip', 'like', "%$search%");
            });
        }

        // Date range
        if ($from = $request->input('from')) $q->whereDate($activityAt, '>=', $from);
        if ($to   = $request->input('to'))   $q->whereDate($activityAt, '<=', $to);

        $q->orderByDesc($activityAt)->orderByDesc('id');
        $clicks = $q->paginate($perPage)->withQueryString();

        // Metrics
        $now = now();
        $todayStart = $now->clone()->startOfDay();
        $tomorrowStart = $todayStart->clone()->addDay();
        $metrics = TrialClick::query()
            ->selectRaw(
                'SUM(CASE WHEN COALESCE(whatsapp_contact_consented_at, created_at) >= ? AND COALESCE(whatsapp_contact_consented_at, created_at) < ? THEN 1 ELSE 0 END) AS today_count,
                 SUM(CASE WHEN COALESCE(whatsapp_contact_consented_at, created_at) >= ? THEN 1 ELSE 0 END) AS last_7_count,
                 SUM(CASE WHEN COALESCE(whatsapp_contact_consented_at, created_at) >= ? THEN 1 ELSE 0 END) AS last_30_count,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS new_count,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS paid_count',
                [
                    $todayStart,
                    $tomorrowStart,
                    $now->clone()->subDays(7),
                    $now->clone()->subDays(30),
                    'new',
                    'paid',
                ]
            )
            ->first();

        $today = (int) ($metrics?->today_count ?? 0);
        $last7 = (int) ($metrics?->last_7_count ?? 0);
        $last30 = (int) ($metrics?->last_30_count ?? 0);
        $newLeads = (int) ($metrics?->new_count ?? 0);
        $paidLeads = (int) ($metrics?->paid_count ?? 0);
        $statuses = TrialClick::STATUSES;

        return view('admin.trial_clicks.index', compact(
            'clicks',
            'today',
            'last7',
            'last30',
            'newLeads',
            'paidLeads',
            'statuses'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'whatsapp_leads_'.now()->format('Ymd_His').'.csv';
        $q = TrialClick::query()->orderByDesc('id');

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            $safeCsvCell = static function ($value) {
                return is_string($value) && preg_match('/^[=+\-@\t\r]/u', $value) === 1
                    ? "'".$value
                    : $value;
            };
            fputcsv($out, [
                'id','lead_code','event_id','created_at','contact_name','phone','contact_consented_at','consent_version','consent_source','consent_locale','click_count','intent','placement','package','vendor','value','currency','status','page','destination','utm_source','utm_medium',
                'utm_campaign','utm_term','utm_content','fbp','fbc','ip','referrer','user_agent'
            ]);
            $q->chunk(1000, function ($rows) use ($out, $safeCsvCell) {
                foreach ($rows as $r) {
                    $row = [
                        $r->id, $r->lead_code, $r->event_id, $r->created_at,
                        $r->contact_name, $r->phone_normalized, $r->whatsapp_contact_consented_at,
                        $r->consent_version, $r->consent_source, $r->consent_locale, $r->click_count,
                        $r->intent, $r->placement, $r->package_name, $r->vendor, $r->value, $r->currency, $r->status,
                        $r->page, $r->destination,
                        $r->utm_source, $r->utm_medium, $r->utm_campaign, $r->utm_term, $r->utm_content,
                        $r->fbp, $r->fbc, $r->ip, $r->referrer, $r->user_agent
                    ];
                    fputcsv($out, array_map($safeCsvCell, $row));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function updateStatus(Request $request, TrialClick $trialClick)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(TrialClick::STATUSES)],
        ]);

        $trialClick->update(['status' => $data['status']]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'status' => $trialClick->status]);
        }

        return back()->with('success', 'Lead status updated.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('trial_ids', []);
        $ids = array_filter(array_map('intval', $ids));
        if ($ids) {
            TrialClick::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected WhatsApp leads deleted.');
        }
        return back()->with('success', 'Nothing selected.');
    }

    public function destroy(TrialClick $trialClick)
    {
        $trialClick->delete();
        return back()->with('success', 'WhatsApp lead deleted.');
    }
}
