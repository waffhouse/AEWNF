<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" 
     class="bg-white border-b border-gray-100 fixed w-full top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>
                
                <!-- Mobile company name display -->
                <div class="sm:hidden flex items-center ml-2 flex-grow">
                    <span class="text-sm font-semibold text-gray-800 truncate">A&E Wholesale of North Florida</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    @can('access admin dashboard')
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Admin Dashboard') }}
                    </x-nav-link>
                    @endcan
                    
                    <!-- Sales History moved to dropdown menu only -->
                    
                    @can('sync netsuite sales data')
                    <x-nav-link :href="route('sales.analytics')" :active="request()->routeIs('sales.analytics')" wire:navigate>
                        {{ __('Sales Analytics') }}
                    </x-nav-link>
                    @endcan
                    
                    @if(!auth()->user()->hasPermissionTo('access admin dashboard') && auth()->user()->hasAnyPermission(['manage orders', 'view all orders']))
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Order Management') }}
                    </x-nav-link>
                    @endif
                    
                    <!-- Users List link removed - users should access user management through the admin dashboard -->
                    
                    
                    <x-nav-link :href="route('inventory.catalog')" :active="request()->routeIs('inventory.catalog')" wire:navigate>
                        {{ __('Product Catalog') }}
                    </x-nav-link>
                    
                    <!-- My Orders moved to dropdown menu only -->
                    
                    @can('add to cart')
                    <x-nav-link :href="route('customer.cart')" :active="request()->routeIs('customer.cart')" wire:navigate class="flex items-center">
                        <span>{{ __('Cart') }}</span>
                        <livewire:cart.cart-counter location="desktop" :showTotal="true" />
                    </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        
                        @can('view own orders')
                        <x-dropdown-link :href="route('customer.orders')" wire:navigate>
                            {{ __('My Web Orders') }}
                        </x-dropdown-link>
                        @endcan
                        
                        @if(auth()->user()->hasAnyPermission(['view netsuite sales data', 'view own orders']))
                        <x-dropdown-link :href="route('sales')" wire:navigate>
                            {{ __('Sales History') }}
                        </x-dropdown-link>
                        @endif
                        
                        @can('sync netsuite sales data')
                        <x-dropdown-link :href="route('sales.analytics')" wire:navigate>
                            {{ __('Sales Analytics') }}
                        </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Cart Icon and Hamburger -->
            <div class="flex items-center sm:hidden space-x-2">
                @can('add to cart')
                <a href="{{ route('customer.cart') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition duration-150 ease-in-out" wire:navigate>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <livewire:cart.cart-counter location="mobile-icon" :showTotal="true" />
                    </div>
                </a>
                @endcan
                
                <!-- Hamburger -->
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu - Slide Out -->
    <div :class="{'translate-x-0': open, '-translate-x-full': ! open}" 
         class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition duration-300 ease-in-out sm:hidden"
         x-cloak>
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" wire:navigate>
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
                <span class="ml-2 text-sm font-semibold text-gray-800">A&E Wholesale of North Florida</span>
            </div>
            <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="pt-2 pb-3 space-y-1 overflow-y-auto max-h-screen">
            <!-- Navigation menu starts here -->
            
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate @click="open = false">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @can('access admin dashboard')
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate @click="open = false">
                {{ __('Admin Dashboard') }}
            </x-responsive-nav-link>
            @endcan
            
            <!-- Sales History moved to user profile dropdown only -->
            
            @can('sync netsuite sales data')
            <x-responsive-nav-link :href="route('sales.analytics')" :active="request()->routeIs('sales.analytics')" wire:navigate @click="open = false">
                {{ __('Sales Analytics') }}
            </x-responsive-nav-link>
            @endcan
            
            @if(!auth()->user()->hasPermissionTo('access admin dashboard') && auth()->user()->hasAnyPermission(['manage orders', 'view all orders']))
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate @click="open = false">
                {{ __('Order Management') }}
            </x-responsive-nav-link>
            @endif
            
            <!-- Users List link removed - users should access user management through the admin dashboard -->
            
            
            <x-responsive-nav-link :href="route('inventory.catalog')" :active="request()->routeIs('inventory.catalog')" wire:navigate @click="open = false">
                {{ __('Product Catalog') }}
            </x-responsive-nav-link>
            
            <!-- My Orders moved to profile section only -->
            
            @can('add to cart')
            <x-responsive-nav-link :href="route('customer.cart')" :active="request()->routeIs('customer.cart')" wire:navigate @click="open = false" class="flex items-center justify-between">
                <span>{{ __('Cart') }}</span>
                <livewire:cart.cart-counter location="mobile" :showTotal="true" />
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate @click="open = false">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                
                @can('view own orders')
                <x-responsive-nav-link :href="route('customer.orders')" wire:navigate @click="open = false">
                    {{ __('My Web Orders') }}
                </x-responsive-nav-link>
                @endcan
                
                @if(auth()->user()->hasAnyPermission(['view netsuite sales data', 'view own orders']))
                <x-responsive-nav-link :href="route('sales')" wire:navigate @click="open = false">
                    {{ __('Sales History') }}
                </x-responsive-nav-link>
                @endif
                
                @can('sync netsuite sales data')
                <x-responsive-nav-link :href="route('sales.analytics')" wire:navigate @click="open = false">
                    {{ __('Sales Analytics') }}
                </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start" @click="open = false">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Backdrop when menu is open -->
    <div x-show="open" 
         @click="open = false"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 z-40 transition-opacity sm:hidden" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
    </div>
</nav>
