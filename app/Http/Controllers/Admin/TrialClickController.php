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
        $q = TrialClick::query();

        // Filters
        if ($search = $request->input('q')) {
            $q->where(function($w) use ($search){
                $w->where('destination','like',"%$search%")
                  ->orWhere('page','like',"%$search%")
                  ->orWhere('fbp','like',"%$search%")
                  ->orWhere('fbc','like',"%$search%")
                  ->orWhere('utm_campaign','like',"%$search%");
            });
        }
        if ($from = $request->input('from')) $q->whereDate('created_at','>=',$from);
        if ($to   = $request->input('to'))   $q->whereDate('created_at','<=',$to);

        $q->orderByDesc('id');

        $clicks = $q->paginate(50)->withQueryString();

        // Simple metrics
        $today  = TrialClick::whereDate('created_at', today())->count();
        $last7  = TrialClick::where('created_at','>=', now()->subDays(7))->count();
        $last30 = TrialClick::where('created_at','>=', now()->subDays(30))->count();

        return view('admin.trial_clicks.index', compact('clicks','today','last7','last30'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'trial_clicks_'.now()->format('Ymd_His').'.csv';

        $q = TrialClick::query()->orderByDesc('id');

        return response()->streamDownload(function() use ($q){
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','event_id','created_at','page','destination','fbp','fbc','ip','utm_source','utm_medium','utm_campaign','utm_term','utm_content','referrer','user_agent']);
            $q->chunk(1000, function($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,$r->event_id,$r->created_at,$r->page,$r->destination,$r->fbp,$r->fbc,$r->ip,
                        $r->utm_source,$r->utm_medium,$r->utm_campaign,$r->utm_term,$r->utm_content,$r->referrer,$r->user_agent
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
