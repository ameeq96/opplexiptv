<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTranslation;
use App\Services\BlogCoverImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request, BlogCoverImageService $coverImages)
    {
        return view('blogs.index', app(\App\Services\Api\PublicSiteDataService::class)->blogs($request, $coverImages));
    }

    public function show(string $slug, BlogCoverImageService $coverImages)
    {
        $data = app(\App\Services\Api\PublicSiteDataService::class)->blog($slug, $coverImages);
        if (isset($data['redirect_url'])) {
            return redirect()->to($data['redirect_url'], 301);
        }
        $data['blog']->increment('views');

        return view('blogs.show', $data);
    }
}
