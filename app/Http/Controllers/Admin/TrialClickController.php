<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrialClick;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrialClickController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10,20,30,40,100], true)) $perPage = 10;

        $q = TrialClick::query();

        // Search
        if ($search = trim((string) $request->input('search'))) {
            $q->where(function ($w) use ($search) {
                $w->where('event_id', 'like', "%$search%")
                  ->orWhere('page', 'like', "%$search%")
                  ->orWhere('destination', 'like', "%$search%")
                  ->orWhere('fbp', 'like', "%$search%")
                  ->orWhere('fbc', 'like', "%$search%")
                  ->orWhere('utm_campaign', 'like', "%$search%")
                  ->orWhere('ip', 'like', "%$search%");
            });
        }

        // Date range
        if ($from = $request->input('from')) $q->whereDate('created_at', '>=', $from);
        if ($to   = $request->input('to'))   $q->whereDate('created_at', '<=', $to);

        $q->orderByDesc('id');
        $clicks = $q->paginate($perPage)->withQueryString();

        // Metrics
        $today  = TrialClick::whereDate('created_at', today())->count();
        $last7  = TrialClick::where('created_at', '>=', now()->subDays(7))->count();
        $last30 = TrialClick::where('created_at', '>=', now()->subDays(30))->count();

        return view('admin.trial_clicks.index', compact('clicks','today','last7','last30'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'trial_clicks_'.now()->format('Ymd_His').'.csv';
        $q = TrialClick::query()->orderByDesc('id');

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','event_id','created_at','page','destination','utm_source','utm_medium',
                'utm_campaign','utm_term','utm_content','fbp','fbc','ip','referrer','user_agent'
            ]);
            $q->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id, $r->event_id, $r->created_at,
                        $r->page, $r->destination,
                        $r->utm_source, $r->utm_medium, $r->utm_campaign, $r->utm_term, $r->utm_content,
                        $r->fbp, $r->fbc, $r->ip, $r->referrer, $r->user_agent
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('trial_ids', []);
        $ids = array_filter(array_map('intval', $ids));
        if ($ids) {
            TrialClick::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected trial clicks deleted.');
        }
        return back()->with('success', 'Nothing selected.');
    }

    public function destroy(TrialClick $trialClick)
    {
        $trialClick->delete();
        return back()->with('success', 'Trial click deleted.');
    }
}
