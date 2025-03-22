<?php

namespace App\Livewire\Cart;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class OrdersList extends Component
{
    public function mount()
    {
        // Check if user is authenticated and has permission to view orders
        if (!Auth::check() || !Auth::user()->can('view own orders')) {
            return redirect()->route('login');
        }
    }

    #[Title('Your Orders')]
    public function render()
    {
        $orders = Auth::user()->orders()->latest()->get();
        
        return view('livewire.cart.orders-list', [
            'orders' => $orders
        ])->layout('layouts.app');
    }
}