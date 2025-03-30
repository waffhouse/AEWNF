<?php

namespace App\Livewire\Admin\Customers;

use App\Livewire\Admin\AdminComponent;

class CustomerManagement extends AdminComponent
{
    public $activeTab = 'list';
    
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view customers'];
    }
    
    /**
     * Switch to the customer sync tab
     */
    public function showSyncTab()
    {
        $this->activeTab = 'sync';
    }
    
    /**
     * Switch to the customer list tab
     */
    public function showListTab()
    {
        $this->activeTab = 'list';
    }
    
    public function render()
    {
        return view('livewire.admin.customers.customer-management');
    }
}
