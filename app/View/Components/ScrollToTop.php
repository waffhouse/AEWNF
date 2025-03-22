<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ScrollToTop extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('components.scroll-to-top');
    }
}