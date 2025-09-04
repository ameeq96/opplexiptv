<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PageTitle extends Component
{
    public string $title;
    public array $breadcrumbs;
    public string $background;
    public bool $rtl;
    public ?string $ariaLabel;

    public function __construct(
        string $title,
        array $breadcrumbs,
        string $background = 'images/background/10.webp',
        bool $rtl = false,
        string $ariaLabel = null
    ) {
        $this->title = $title;
        $this->breadcrumbs = $breadcrumbs;
        $this->background = $background;
        $this->rtl = $rtl;
        $this->ariaLabel = $ariaLabel;
    }

    public function render()
    {
        return view('components.page-title');
    }
}
