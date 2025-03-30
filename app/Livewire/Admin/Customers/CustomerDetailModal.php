<?php

namespace App\Livewire\Admin\Customers;

use App\Livewire\Admin\AdminComponent;
use App\Models\Customer;
use Livewire\Attributes\On;

class CustomerDetailModal extends AdminComponent
{
    public $showModal = false;
    public $customer = null;
    public $customerId = null;
    
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view customers'];
    }
    
    /**
     * Listen for the show-customer-details event
     */
    #[On('show-customer-details')]
    public function showCustomerDetails($data)
    {
        // Extract the customerId from the data array
        $customerId = $data['customerId'] ?? null;
        
        if ($customerId) {
            $this->customerId = $customerId;
            $this->customer = Customer::findOrFail($customerId);
            $this->showModal = true;
            
            // For debugging
            // \Log::info("CustomerDetailModal received customer ID: $customerId");
            // \Log::info("Modal should now be shown: " . ($this->showModal ? 'Yes' : 'No'));
        }
    }
    
    /**
     * Close the modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->customer = null;
        $this->customerId = null;
        
        // For debugging
        \Log::info("Modal closed. showModal = " . ($this->showModal ? 'true' : 'false'));
    }
    
    /**
     * Set the customer to the given ID
     * 
     * @param int $customerId
     */
    public function setCustomer($customerId)
    {
        try {
            $this->customer = Customer::findOrFail($customerId);
            $this->customerId = $customerId;
            $this->showModal = true;
            
            // For debugging
            \Log::info("Customer set to ID: $customerId. showModal = " . ($this->showModal ? 'true' : 'false'));
        } catch (\Exception $e) {
            \Log::error("Error loading customer: " . $e->getMessage());
        }
    }
    
    /**
     * Listen for the close-modal event to close the modal
     */
    #[On('close-modal')]
    public function handleCloseModal($modalName)
    {
        if ($modalName === 'customer-detail-modal') {
            $this->closeModal();
        }
    }
    
    public function render()
    {
        return view('livewire.admin.customers.customer-detail-modal');
    }
}