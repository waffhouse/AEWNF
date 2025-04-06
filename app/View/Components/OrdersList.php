<?php

namespace App\View\Components;

use Illuminate\View\Component;

class OrdersList extends Component
{
    /**
     * The orders to display.
     *
     * @var \Illuminate\Database\Eloquent\Collection|\App\Models\Order[]|array
     */
    public $orders;

    /**
     * Whether this is being rendered for admin view.
     *
     * @var bool
     */
    public $isAdmin;

    /**
     * Total count of orders.
     *
     * @var int
     */
    public $totalCount;

    /**
     * Count of loaded orders.
     *
     * @var int
     */
    public $loadedCount;

    /**
     * Whether there are more orders to load.
     *
     * @var bool
     */
    public $hasMorePages;

    /**
     * Whether orders are currently loading.
     *
     * @var bool
     */
    public $isLoading;

    /**
     * Search term for filtering orders.
     *
     * @var string
     */
    public $search;

    /**
     * Create a new component instance.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|\App\Models\Order[]|array  $orders
     * @param  bool  $isAdmin
     * @param  int|null  $totalCount
     * @param  int|null  $loadedCount
     * @param  bool|null  $hasMorePages
     * @param  bool|null  $isLoading
     * @param  string|null  $search
     * @return void
     */
    public function __construct($orders, $isAdmin = false, $totalCount = null, $loadedCount = null, $hasMorePages = null, $isLoading = false, $search = '')
    {
        $this->orders = $orders;
        $this->isAdmin = $isAdmin;
        $this->totalCount = $totalCount ?? (is_countable($orders) ? count($orders) : 0);
        $this->loadedCount = $loadedCount ?? (is_countable($orders) ? count($orders) : 0);
        $this->hasMorePages = $hasMorePages ?? false;
        $this->isLoading = $isLoading ?? false;
        $this->search = $search;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.orders-list');
    }
}
