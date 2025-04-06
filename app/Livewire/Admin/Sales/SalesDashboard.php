<?php

namespace App\Livewire\Admin\Sales;

use App\Traits\AdminAuthorization;
use Livewire\Component;

class SalesDashboard extends Component
{
    use AdminAuthorization;

    protected $listeners = [
        'salesSyncCompleted' => '$refresh',
    ];

    public function mount()
    {
        $this->authorize('sync netsuite sales data');
    }

    public function render()
    {
        return view('livewire.admin.sales.sales-dashboard')
            ->layout('layouts.app');
    }
}
