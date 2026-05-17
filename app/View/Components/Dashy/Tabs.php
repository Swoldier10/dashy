<?php

namespace App\View\Components\Dashy;

use Illuminate\View\Component;
use Illuminate\View\View;

class Tabs extends Component
{
    public function __construct(
        public ?string $name = null,
        public ?string $wireModel = null,
        public ?string $defaultValue = null,
    ) {}

    public function render(): View
    {
        return view('components.dashy.tabs');
    }
}
