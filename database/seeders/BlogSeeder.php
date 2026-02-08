<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;
use App\Models\BlogTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $locales = config('app.locales', ['en']);
        $fallback = config('app.fallback_locale', 'en');

        $categorySeeds = [
            'product-updates' => 'Product Updates',
            'news' => 'News',
            'academy' => 'Academy',
            'client-management' => 'Client Management',
            'features' => 'Features',
        ];

        $categories = collect();
        foreach ($categorySeeds as $seedSlug => $seedTitle) {
            $category = BlogCategory::query()->create();
            foreach ($locales as $locale) {
                $title = $seedTitle;
                if ($locale !== 'en') {
                    $title = $seedTitle; // keep english if translation not available
                }
                $slug = Str::slug($title) ?: ($seedSlug . '-' . $locale);
                BlogCategoryTranslation::query()->create([
                    'blog_category_id' => $category->id,
                    'locale' => $locale,
                    'title' => $title,
                    'slug' => $slug,
                ]);
            }
            $categories->push($category);
        }

        for ($i = 1; $i <= 10; $i++) {
            $blog = Blog::query()->create([
                'author_id' => null,
                'cover_image' => null,
                'status' => $i % 5 === 0 ? 'draft' : 'published',
                'published_at' => $i % 5 === 0 ? null : now()->subDays(rand(1, 30)),
                'reading_time' => rand(3, 10),
                'views' => rand(50, 200),
                'is_featured' => $i % 4 === 0,
            ]);

            $attach = $categories->random(rand(1, 2));
            $blog->categories()->attach($attach->pluck('id')->all());

            foreach ($locales as $locale) {
                $faker = FakerFactory::create($locale);
                $title = $faker->sentence(6);
                $slug = Str::slug($title);
                if ($slug === '') {
                    $slug = 'blog-' . $blog->id . '-' . $locale . '-' . Str::random(4);
                }

                BlogTranslation::query()->create([
                    'blog_id' => $blog->id,
                    'locale' => $locale,
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $faker->sentence(14),
                    'content' => '<p>' . implode('</p><p>', $faker->paragraphs(5)) . '</p>',
                    'seo_title' => null,
                    'seo_description' => null,
                    'seo_keywords' => null,
                    'og_title' => null,
                    'og_description' => null,
                    'og_image' => null,
                    'canonical_url' => null,
                    'schema_json' => null,
                ]);
            }
        }
    }
}