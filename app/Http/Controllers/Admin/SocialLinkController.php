<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        $links = SocialLink::query()->orderBy('sort_order')->get();

        return view('admin.social-links.index', [
            'links' => $links,
        ]);
    }

    public function create()
    {
        return view('admin.social-links.create', [
            'link' => new SocialLink(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        SocialLink::create($data);

        return redirect()->route('admin.social-links.index')->with('success', 'Social link added.');
    }

    public function edit(SocialLink $social_link)
    {
        return view('admin.social-links.edit', [
            'link' => $social_link,
        ]);
    }

    public function update(Request $request, SocialLink $social_link)
    {
        $data = $this->validateData($request);
        $social_link->update($data);

        return redirect()->route('admin.social-links.index')->with('success', 'Social link updated.');
    }

    public function destroy(SocialLink $social_link)
    {
        $social_link->delete();

        return redirect()->route('admin.social-links.index')->with('success', 'Social link deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'platform' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:500'],
            'icon_class' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
