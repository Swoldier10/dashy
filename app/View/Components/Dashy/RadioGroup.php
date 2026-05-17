<?php

namespace App\View\Components\Dashy;

use Illuminate\View\Component;
use Illuminate\View\View;

class RadioGroup extends Component
{
    public function __construct(
        public ?string $name = null,
        public string $variant = 'stacked', // segmented | stacked
        public ?string $wireModel = null,
    ) {}

    public function render(): View
    {
        return view('components.dashy.radio-group');
    }
}
