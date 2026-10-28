<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Blogs\BlogContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandingBlogController extends Controller
{
    public function index(Request $request, string $brand, BlogContentService $content): JsonResponse
    {
        $data = $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:25'],
            'revision' => ['nullable', 'string', 'regex:/^[a-f0-9]{64}$/D'],
        ]);

        return response()->json(['data' => $content->feed($brand, (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 25), $data['revision'] ?? null)])->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, string $brand, string $slug, BlogContentService $content): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['nullable', 'string', Rule::in(config('blog-publishing.supported_locales', ['en']))],
        ]);

        return response()->json(['data' => $content->article($brand, $slug, $data['locale'] ?? 'en')])
            ->header('Cache-Control', 'no-store');
    }
}
