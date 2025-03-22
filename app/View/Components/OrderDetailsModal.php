<?php

namespace App\View\Components;

use App\Models\Order;
use Illuminate\View\Component;

class OrderDetailsModal extends Component
{
    /**
     * The order to display.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Create a new component instance.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.order-details-modal');
    }
}