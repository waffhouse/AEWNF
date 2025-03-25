<?php

namespace App\Livewire\Admin\Sales;

use App\Traits\AdminAuthorization;
use Livewire\Component;

class SalesDashboard extends Component
{
    use AdminAuthorization;
    
    public function mount()
    {
        $this->authorize('view netsuite sales data');
    }
    
    public function render()
    {
        return view('livewire.admin.sales.sales-dashboard')
            ->layout('layouts.app');
    }
}