<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MaintenanceController extends Controller
{
    private const ACTIONS = [
        'full' => [
            'label' => 'Full Maintenance',
            'description' => 'Clear optimization caches, rebuild production caches, then restart queue workers.',
            'commands' => [
                'optimize:clear',
                'route:trans:clear',
                'config:cache',
                'route:trans:cache',
                'view:cache',
                'queue:restart',
            ],
            'button' => 'Run Full Maintenance',
            'style' => 'primary',
            'icon' => 'bi-tools',
        ],
        'optimize-clear' => [
            'label' => 'Clear Optimization Caches',
            'description' => 'Clear cached configuration, routes, events, views, and compiled files.',
            'commands' => ['optimize:clear'],
            'button' => 'Clear Optimization Caches',
            'style' => 'outline-danger',
            'icon' => 'bi-trash3',
        ],
        'application-cache-clear' => [
            'label' => 'Clear Application Cache',
            'description' => 'Remove values stored in the application cache.',
            'commands' => ['cache:clear'],
            'button' => 'Clear Application Cache',
            'style' => 'outline-warning',
            'icon' => 'bi-database-x',
        ],
        'config-clear' => [
            'label' => 'Clear Configuration Cache',
            'description' => 'Remove the cached configuration file so environment changes can be read again.',
            'commands' => ['config:clear'],
            'button' => 'Clear Configuration Cache',
            'style' => 'outline-secondary',
            'icon' => 'bi-gear',
        ],
        'config-cache' => [
            'label' => 'Cache Configuration',
            'description' => 'Rebuild the optimized configuration cache for production.',
            'commands' => ['config:cache'],
            'button' => 'Cache Configuration',
            'style' => 'outline-success',
            'icon' => 'bi-gear-wide-connected',
        ],
        'route-clear' => [
            'label' => 'Clear Multilingual Route Cache',
            'description' => 'Remove the translated route cache files for every locale.',
            'commands' => ['route:trans:clear'],
            'button' => 'Clear Multilingual Route Cache',
            'style' => 'outline-secondary',
            'icon' => 'bi-signpost-split',
        ],
        'route-cache' => [
            'label' => 'Cache Multilingual Routes',
            'description' => 'Rebuild the project-compatible route cache for every locale.',
            'commands' => ['route:trans:cache'],
            'button' => 'Cache Multilingual Routes',
            'style' => 'outline-success',
            'icon' => 'bi-signpost-2',
        ],
        'view-clear' => [
            'label' => 'Clear Compiled Views',
            'description' => 'Delete compiled Blade templates so they are generated again when requested.',
            'commands' => ['view:clear'],
            'button' => 'Clear Compiled Views',
            'style' => 'outline-secondary',
            'icon' => 'bi-window-x',
        ],
        'view-cache' => [
            'label' => 'Cache Views',
            'description' => 'Precompile Blade templates for faster production rendering.',
            'commands' => ['view:cache'],
            'button' => 'Cache Views',
            'style' => 'outline-success',
            'icon' => 'bi-window-check',
        ],
        'event-clear' => [
            'label' => 'Clear Event Cache',
            'description' => 'Remove the cached event and listener manifest.',
            'commands' => ['event:clear'],
            'button' => 'Clear Event Cache',
            'style' => 'outline-secondary',
            'icon' => 'bi-lightning',
        ],
        'event-cache' => [
            'label' => 'Cache Events',
            'description' => 'Discover and cache application events and listeners.',
            'commands' => ['event:cache'],
            'button' => 'Cache Events',
            'style' => 'outline-success',
            'icon' => 'bi-lightning-charge',
        ],
        'queue-restart' => [
            'label' => 'Restart Queue Workers',
            'description' => 'Ask active queue workers to exit gracefully after their current job.',
            'commands' => ['queue:restart'],
            'button' => 'Restart Queue Workers',
            'style' => 'outline-primary',
            'icon' => 'bi-arrow-repeat',
        ],
    ];

    public function index(): View
    {
        return view('admin.maintenance.index', ['actions' => self::ACTIONS]);
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in(array_keys(self::ACTIONS))],
        ]);

        return $this->execute($validated['action']);
    }

    public function runFull(): RedirectResponse
    {
        return $this->execute('full');
    }

    private function execute(string $key): RedirectResponse
    {
        $action = self::ACTIONS[$key];

        try {
            foreach ($action['commands'] as $command) {
                if (Artisan::call($command) !== 0) {
                    throw new RuntimeException("Maintenance command failed: {$command}");
                }
            }
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.maintenance.index')->with(
                'error',
                'Maintenance could not be completed. Check the application log.'
            );
        }

        return redirect()->route('admin.maintenance.index')->with(
            'success',
            "{$action['label']} completed successfully."
        );
    }
}
