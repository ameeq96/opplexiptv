<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogGenerationRun;
use App\Models\BlogSiteSetting;
use App\Services\Blogs\BlogPublicationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogAutomationController extends Controller
{
    public function index(Request $request, BlogPublicationService $publication)
    {
        $sites = config('blog-publishing.sites', []);
        $request->validate(['site_key' => ['nullable', Rule::in(array_keys($sites))]]);
        $publication->initializeSettings();
        $siteKey = $request->query('site_key');

        return view('admin.blogs.automation', [
            'settings' => BlogSiteSetting::query()->orderBy('id')->get(),
            'runs' => BlogGenerationRun::query()->with('deliveries')
                ->when($siteKey, fn ($query) => $query->where('site_key', $siteKey))->latest('id')->limit(50)->get(),
            'sites' => $sites, 'siteKey' => $siteKey,
            'canManage' => (bool) $request->user('admin')?->isOwner(),
        ]);
    }

    public function update(Request $request, string $siteKey, BlogPublicationService $publication)
    {
        abort_unless($request->user('admin')?->isOwner(), 403);
        abort_unless(array_key_exists($siteKey, config('blog-publishing.sites', [])), 404);
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $publication->initializeSettings();
        BlogSiteSetting::query()->where('site_key', $siteKey)->firstOrFail()->update(['enabled' => $data['enabled']]);

        return redirect()->route('admin.blogs.automation.index')->with('success', 'Website publishing preference saved.');
    }

    public function retry(Request $request, BlogGenerationRun $run, BlogPublicationService $publication)
    {
        abort_unless($request->user('admin')?->isOwner(), 403);
        $publication->retry($run);

        return redirect()->route('admin.blogs.automation.index')->with('success', 'Publication retry queued for the daily agent.');
    }
}
