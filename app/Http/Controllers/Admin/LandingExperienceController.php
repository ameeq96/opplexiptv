<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerReview;
use App\Models\LandingPageContent;
use App\Models\ServiceIncident;
use App\Models\SetupMedia;
use App\Services\LandingExperienceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LandingExperienceController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['brand' => ['nullable', Rule::in(array_keys(LandingPageContent::BRANDS))]]);
        $brand = $data['brand'] ?? null;
        $filter = fn ($query) => $query->when($brand, fn ($q) => $q->where('brand', $brand))->latest('id');

        return view('admin.landing-experience.index', [
            'incidents' => $filter(ServiceIncident::query())->limit(50)->get(),
            'media' => $filter(SetupMedia::query())->limit(50)->get(),
            'reviews' => $filter(CustomerReview::query())->limit(100)->get(),
            'brands' => LandingPageContent::BRANDS, 'locales' => config('app.locales', ['en']), 'brand' => $brand,
        ]);
    }

    public function storeIncident(Request $request)
    {
        $this->saveIncident($request, new ServiceIncident());
        return $this->saved('Incident saved. Only explicitly published updates appear on the matching brand and language.');
    }

    public function updateIncident(Request $request, ServiceIncident $incident)
    {
        $this->saveIncident($request, $incident);
        return $this->saved('Incident updated.');
    }

    public function storeMedia(Request $request, LandingExperienceService $experience)
    {
        $this->saveMedia($request, new SetupMedia(), $experience);
        return $this->saved('Setup media record saved. No tutorial evidence is generated or invented.');
    }

    public function updateMedia(Request $request, SetupMedia $media, LandingExperienceService $experience)
    {
        $this->saveMedia($request, $media, $experience);
        return $this->saved('Setup media record updated.');
    }

    public function moderateReview(Request $request, CustomerReview $review, LandingExperienceService $experience)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['publish', 'reject'])],
            'moderation_note' => ['nullable', 'string', 'max:2000', $this->plainText()],
        ]);
        DB::transaction(function () use ($request, $review, $experience, $data) {
            $review = CustomerReview::query()->lockForUpdate()->findOrFail($review->id);
            if ($data['action'] === 'publish' && (!$review->publication_consented_at || $review->withdrawn_at || !$experience->reviewEligible($review))) {
                throw ValidationException::withMessages(['action' => 'An eligible production purchase and current publication permission are required.']);
            }
            $review->update([
                'status' => $data['action'] === 'publish' ? 'published' : 'rejected',
                'published_at' => $data['action'] === 'publish' ? now() : null,
                'moderated_at' => now(), 'moderated_by_admin_id' => $request->user('admin')->id,
                'moderation_note' => $data['moderation_note'] ?? null,
            ]);
        });

        return $this->saved('Review moderation saved.');
    }

    private function saveIncident(Request $request, ServiceIncident $incident): void
    {
        $data = $request->validate($this->baseRules() + [
            'title' => ['required', 'string', 'max:160', $this->plainText()],
            'body' => ['required', 'string', 'max:4000', $this->plainText()],
            'state' => ['required', Rule::in(ServiceIncident::STATES)],
            'started_at' => ['required', 'date', 'before_or_equal:now'],
            'resolved_at' => ['required_if:state,resolved', 'nullable', 'date', 'after_or_equal:started_at', 'before_or_equal:now'],
            'is_published' => ['required', 'boolean'],
        ]);
        if ($data['state'] !== 'resolved' && !empty($data['resolved_at'])) {
            throw ValidationException::withMessages(['resolved_at' => 'Only a resolved incident can have a resolution time.']);
        }
        $incident->fill($data + ['updated_by_admin_id' => $request->user('admin')->id]);
        $incident->published_at = $data['is_published'] ? ($incident->published_at ?: now()) : null;
        $incident->save();
    }

    private function saveMedia(Request $request, SetupMedia $media, LandingExperienceService $experience): void
    {
        $rules = $this->baseRules() + [
            'guide_slug' => ['required', 'string', 'max:160', 'regex:~^(?:blogs/)?[a-z0-9]+(?:-[a-z0-9]+)*$~D'],
            'kind' => ['required', Rule::in(['image', 'video'])],
            'media_url' => ['required', 'string', 'max:500'],
            'tested_at' => ['required', 'date', 'before_or_equal:now'],
            'publication_permission' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ];
        foreach (['title' => 160, 'description' => 2000, 'alt_text' => 240, 'device_model' => 160,
            'player_name' => 120, 'player_version' => 80, 'evidence_reference' => 191] as $field => $max) {
            $rules[$field] = ['required', 'string', 'max:'.$max, $this->plainText()];
        }
        $data = $request->validate($rules);
        if (!$experience->mediaUrlAllowed($data['media_url'], $data['kind'])) {
            throw ValidationException::withMessages(['media_url' => 'Use an existing owned HTTPS image/video under opplexiptv.com/images/ or /storage/. External embeds, scripts and missing files are not accepted.']);
        }
        $pages = json_decode(file_get_contents(resource_path('content/landing-pages/'.$data['locale'].'.json')), true);
        if (!collect($pages['brands'][$data['brand']]['pages'] ?? [])->contains('slug', $data['guide_slug'])) {
            throw ValidationException::withMessages(['guide_slug' => 'Choose an existing page for this brand and language.']);
        }
        if ($data['is_published'] && !$data['publication_permission']) {
            throw ValidationException::withMessages(['publication_permission' => 'Recorded permission is required before publishing media.']);
        }
        $permission = $data['publication_permission'];
        unset($data['publication_permission']);
        $media->fill($data + ['updated_by_admin_id' => $request->user('admin')->id]);
        $media->publication_permission_at = $permission ? ($media->publication_permission_at ?: now()) : null;
        $media->published_at = $data['is_published'] ? ($media->published_at ?: now()) : null;
        $media->save();
    }

    private function baseRules(): array
    {
        return [
            'brand' => ['required', Rule::in(array_keys(LandingPageContent::BRANDS))],
            'locale' => ['required', Rule::in(config('app.locales', ['en']))],
        ];
    }

    private function plainText(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && (trim($value) === '' || strip_tags($value) !== $value
                || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value))) {
                $fail('Use non-empty plain text without HTML or control characters.');
            }
        };
    }

    private function saved(string $message)
    {
        return redirect()->route('admin.landing-experience.index')->with('success', $message);
    }
}
