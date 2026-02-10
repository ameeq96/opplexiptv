<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use Illuminate\Http\Request;

class FooterLinkController extends Controller
{
    public function index()
    {
        $links = FooterLink::query()->orderBy('group')->orderBy('sort_order')->get();

        return view('admin.footer-links.index', [
            'links' => $links,
        ]);
    }

    public function create()
    {
        return view('admin.footer-links.create', [
            'link' => new FooterLink(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        FooterLink::create($data);

        return redirect()->route('admin.footer-links.index')->with('success', 'Footer link added.');
    }

    public function edit(FooterLink $footer_link)
    {
        return view('admin.footer-links.edit', [
            'link' => $footer_link,
        ]);
    }

    public function update(Request $request, FooterLink $footer_link)
    {
        $data = $this->validateData($request);
        $footer_link->update($data);

        return redirect()->route('admin.footer-links.index')->with('success', 'Footer link updated.');
    }

    public function destroy(FooterLink $footer_link)
    {
        $footer_link->delete();

        return redirect()->route('admin.footer-links.index')->with('success', 'Footer link deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'group' => ['required', 'in:explore,company,legal,deeplink'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
