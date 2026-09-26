<?php

namespace Tests\Feature\Performance;

use Illuminate\Routing\Route;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class CoreWebVitalsTest extends TestCase
{
    public function test_head_renders_session_window_cls_and_interaction_grouped_inp_tracking(): void
    {
        $route = new Route(['GET'], '/', []);
        $route->name('home');
        request()->setRouteResolver(fn () => $route);

        $html = view('includes.head', [
            'displayMovies' => collect(),
            'isRtl' => false,
        ])->render();

        foreach ([
            "entry.startTime - last.startTime < 1000",
            "entry.startTime - first.startTime < 5000",
            "metrics.CLS = Math.max(metrics.CLS, clsWindowValue)",
            "interactionDurations.get(entry.interactionId)",
            "interactionDurations.set(entry.interactionId, Math.max(previousDuration, entry.duration))",
            "Math.floor(durations.length / 50)",
            "var limits = name === 'LCP' ? [2500, 4000] : (name === 'INP' ? [200, 500] : [0.1, 0.25])",
        ] as $marker) {
            $this->assertStringContainsString($marker, $html);
        }

        $this->assertStringContainsString("observed.CLS = true", $html);
        $this->assertStringContainsString("if (sent[name] || !observed[name]) return", $html);
        $this->assertStringNotContainsString("if (sent[name] || !metrics[name]) return", $html);
        $this->assertStringContainsString("window.trackMarketingEvent('web_vital'", $html);
    }

    public function test_tracker_calculates_synthetic_cls_and_inp_samples(): void
    {
        $tracker = $this->trackerScript();
        $metrics = $this->runTracker($tracker, <<<'JS'
observers['largest-contentful-paint'].callback(entries([{ startTime: 2100 }]));
observers['layout-shift'].callback(entries([
    { startTime: 0, value: 0.10, hadRecentInput: false },
    { startTime: 500, value: 0.20, hadRecentInput: false },
    { startTime: 2000, value: 0.25, hadRecentInput: false },
    { startTime: 2300, value: 0.10, hadRecentInput: false },
    { startTime: 2500, value: 5, hadRecentInput: true }
]));
const interactions = [{ interactionId: 1, duration: 900 }];
for (let id = 2; id <= 50; id += 1) interactions.push({ interactionId: id, duration: 100 });
interactions.push({ interactionId: 2, duration: 130 });
observers.event.callback(entries(interactions));
listeners.pagehide();
JS);

        $this->assertSame(2100, $metrics['LCP']['metric_value']);
        $this->assertSame('good', $metrics['LCP']['metric_rating']);
        $this->assertSame(130, $metrics['INP']['metric_value']);
        $this->assertSame('good', $metrics['INP']['metric_rating']);
        $this->assertSame(0.35, $metrics['CLS']['metric_value']);
        $this->assertSame('poor', $metrics['CLS']['metric_rating']);
    }

    public function test_tracker_reports_zero_cls_when_no_layout_shift_occurs(): void
    {
        $metrics = $this->runTracker($this->trackerScript(), 'listeners.pagehide();');

        $this->assertSame(['CLS'], array_keys($metrics));
        $this->assertSame(0, $metrics['CLS']['metric_value']);
        $this->assertSame('good', $metrics['CLS']['metric_rating']);
    }

    private function trackerScript(): string
    {
        $route = new Route(['GET'], '/', []);
        $route->name('home');
        request()->setRouteResolver(fn () => $route);

        $html = view('includes.head', [
            'displayMovies' => collect(),
            'isRtl' => false,
        ])->render();

        $this->assertSame(1, preg_match(
            "/(\\(function \\(\\) \\{\\s*if \\(!\\('PerformanceObserver' in window\\)\\) return;.*?\\}\\)\\(\\);)/s",
            $html,
            $matches
        ));

        return $matches[1];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function runTracker(string $tracker, string $scenario): array
    {
        $process = new Process(['node']);
        $process->setInput(<<<JS
const observers = {};
const listeners = {};
const reported = [];
globalThis.window = globalThis;
globalThis.location = { pathname: '/synthetic-cwv' };
globalThis.document = {
    visibilityState: 'visible',
    addEventListener: (name, callback) => { listeners[name] = callback; }
};
globalThis.addEventListener = (name, callback) => { listeners[name] = callback; };
globalThis.PerformanceObserver = class {
    constructor(callback) { this.callback = callback; }
    observe(options) { observers[options.type] = this; }
};
globalThis.trackMarketingEvent = (name, payload) => { reported.push({ name, payload }); };
const entries = (values) => ({ getEntries: () => values });
{$tracker}
{$scenario}
const metrics = Object.fromEntries(reported.map((item) => [item.payload.metric_name, item.payload]));
process.stdout.write(JSON.stringify(metrics));
JS);
        $process->setTimeout(10);
        $process->run();

        if (! $process->isSuccessful() && str_contains($process->getErrorOutput(), 'not recognized')) {
            $this->markTestSkipped('Node.js is required for the synthetic Core Web Vitals test.');
        }

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    }
}
