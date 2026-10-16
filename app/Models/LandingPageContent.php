<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageContent extends Model
{
    public const BRANDS = [
        'trex' => 'Trex IPTV 8K',
        'opplex' => 'Opplex IPTV',
        'geo' => 'Geo IPTV',
        '5g' => '5G Live IPTV',
    ];

    protected $fillable = [
        'brand', 'locale', 'hero_heading', 'hero_description', 'guide_heading', 'guide_intro',
        'guide_sections', 'faqs', 'highlights', 'is_published',
    ];

    protected $casts = [
        'guide_sections' => 'array',
        'faqs' => 'array',
        'highlights' => 'array',
        'is_published' => 'boolean',
    ];

    public function publicContent(): array
    {
        return $this->only([
            'hero_heading', 'hero_description', 'guide_heading', 'guide_intro',
            'guide_sections', 'faqs', 'highlights',
        ]);
    }
}
