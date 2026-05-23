<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PageTitle extends Component
{
    public string $title;

    public array $breadcrumbs;

    public string $background;

    public string $desktopBackground;

    public string $mobileBackground;

    public bool $rtl;

    public ?string $ariaLabel;

    public function __construct(
        string $title,
        array $breadcrumbs,
        string $background = 'images/background/10.webp',
        bool $rtl = false,
        ?string $ariaLabel = null
    ) {
        $this->title = $title;
        $this->breadcrumbs = $breadcrumbs;
        $this->background = $background;
        $this->desktopBackground = $this->optimizedBackground($background, 'lcp') ?? $background;
        $this->mobileBackground = $this->optimizedBackground($background, 'mobile') ?? $this->desktopBackground;
        $this->rtl = $rtl;
        $this->ariaLabel = $ariaLabel;
    }

    private function optimizedBackground(string $background, string $suffix): ?string
    {
        $path = preg_replace('/\.webp$/', "-{$suffix}.webp", $background);

        if (! $path || $path === $background) {
            return null;
        }

        return is_file(public_path($path)) ? $path : null;
    }

    public function render()
    {
        return view('components.page-title');
    }
}
