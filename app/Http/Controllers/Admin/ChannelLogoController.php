<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChannelLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelLogoController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', '');

        $query = ChannelLogo::query();
        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $logos = $query->orderBy('sort_order')->orderByDesc('id')->paginate(24)->withQueryString();

        return view('admin.channel-logos.index', [
            'logos' => $logos,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('admin.channel-logos.create', [
            'logo' => new ChannelLogo(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['image'] = $this->storeImage($request);

        ChannelLogo::create($data);

        return redirect()->route('admin.channel-logos.index')->with('success', 'Logo added.');
    }

    public function edit(ChannelLogo $channel_logo)
    {
        return view('admin.channel-logos.edit', [
            'logo' => $channel_logo,
        ]);
    }

    public function update(Request $request, ChannelLogo $channel_logo)
    {
        $data = $this->validateData($request, false);

        if ($request->hasFile('image')) {
            $this->deleteImage($channel_logo->image);
            $data['image'] = $this->storeImage($request);
        }

        $channel_logo->update($data);

        return redirect()->route('admin.channel-logos.index')->with('success', 'Logo updated.');
    }

    public function destroy(ChannelLogo $channel_logo)
    {
        $this->deleteImage($channel_logo->image);
        $channel_logo->delete();

        return redirect()->route('admin.channel-logos.index')->with('success', 'Logo deleted.');
    }

    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];

        $rules['image'] = $isCreate
            ? ['required', 'image', 'mimes:webp,jpg,jpeg,png,svg', 'max:2048']
            : ['nullable', 'image', 'mimes:webp,jpg,jpeg,png,svg', 'max:2048'];

        $data = $request->validate($rules);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function storeImage(Request $request): string
    {
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension();
        $filename = 'logo-' . Str::random(8) . '-' . time() . '.' . $ext;

        $dest = public_path('images/resource');
        if (!is_dir($dest)) {
            mkdir($dest, 0775, true);
        }

        $file->move($dest, $filename);

        return 'images/resource/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }
        $full = public_path($path);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
