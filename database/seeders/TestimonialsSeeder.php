<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['text' => __('messages.testimonial_1'),  'author_name' => 'Amaan Khalid',  'image' => 'images/img-test-2.webp'],
            ['text' => __('messages.testimonial_2'),  'author_name' => 'Nouman Shahid', 'image' => 'images/img-test-3.webp'],
            ['text' => __('messages.testimonial_3'),  'author_name' => 'Michael',       'image' => 'images/resource/author-1.webp'],
            ['text' => __('messages.testimonial_4'),  'author_name' => 'Sarah',         'image' => 'images/resource/author-2.webp'],
            ['text' => __('messages.testimonial_5'),  'author_name' => 'Ameeq Khan',    'image' => 'images/img-test.webp'],
            ['text' => __('messages.testimonial_6'),  'author_name' => 'Luc Dubois',    'image' => 'images/resource/author-3.webp'],
            ['text' => __('messages.testimonial_7'),  'author_name' => 'Giulia Romano', 'image' => 'images/resource/author-5.webp'],
            ['text' => __('messages.testimonial_8'),  'author_name' => 'Oliver Smith',  'image' => 'images/resource/author-6.webp'],
            ['text' => __('messages.testimonial_9'),  'author_name' => 'Fatima B.',     'image' => 'images/resource/author-7.webp'],
            ['text' => __('messages.testimonial_10'), 'author_name' => 'Marco L.',      'image' => 'images/resource/author-8.webp'],
        ];

        foreach ($items as $i => $item) {
            Testimonial::updateOrCreate(
                ['author_name' => $item['author_name']],
                $item + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
