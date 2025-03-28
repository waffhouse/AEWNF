<?php

namespace App\Livewire\Modals;

use App\Models\Sale;
use Livewire\Component;

class TransactionDetailModal extends Component
{
    public ?int $saleId = null;
    public ?Sale $transaction = null;
    public bool $show = false;
    
    protected $listeners = [
        'showTransactionDetail' => 'showTransaction',
    ];
    
    public function showTransaction($saleId)
    {
        // Handle both array parameter (from Livewire.dispatch(['id'])) and direct parameter
        if (is_array($saleId)) {
            $saleId = $saleId[0] ?? null;
        }
        
        $this->saleId = $saleId;
        $this->loadTransaction();
        $this->show = true;
    }
    
    protected function loadTransaction()
    {
        if ($this->saleId) {
            $this->transaction = Sale::with('items')->find($this->saleId);
        }
    }
    
    public function close()
    {
        // Remove the body lock through inline JavaScript for immediate effect
        $this->js('document.body.classList.remove("overflow-hidden")'); 
        
        $this->show = false;
        $this->transaction = null;
        $this->saleId = null;
    }
    
    public function render()
    {
        // If the modal is not being shown, ensure body scroll is restored
        if (!$this->show) {
            $this->js('document.body.classList.remove("overflow-hidden")'); 
        }
        
        return view('livewire.modals.transaction-detail-modal');
    }
}