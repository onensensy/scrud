<?php

namespace Sensy\Crud\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // dd("HERE");
        return view('scrud::layouts.admin');
    }
}
