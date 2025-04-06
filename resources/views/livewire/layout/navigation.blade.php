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
     class="bg-gradient-to-r from-red-700 to-red-600 fixed w-full top-0 z-40 shadow-md">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}" wire:navigate class="flex items-center">
                        <div class="flex items-center justify-center bg-white rounded-md p-2 shadow-sm">
                            <x-application-logo class="block h-7 w-auto" />
                        </div>
                        <div class="ml-3 pl-3 border-l border-red-400 hidden sm:block">
                            <div class="text-sm font-semibold text-white">A&E Wholesale of North Florida</div>
                            <div class="text-xs text-red-100">Business Portal</div>
                        </div>
                    </a>
                </div>
                
                <!-- Mobile company name display -->
                <div class="sm:hidden flex items-center ml-2 flex-grow">
                    <span class="text-sm font-medium text-white truncate">A&E Wholesale</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex space-x-1 sm:ms-4 sm:items-center h-full">
                    @auth
                    <a href="{{ route('dashboard') }}" wire:navigate 
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('dashboard') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        {{ __('Dashboard') }}
                    </a>
                    
                    @can('access admin dashboard')
                    <a href="{{ route('admin.dashboard') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        {{ __('Admin') }}
                    </a>
                    @endcan
                    
                    @can('sync netsuite sales data')
                    <a href="{{ route('sales.analytics') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('sales.analytics') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        {{ __('Analytics') }}
                    </a>
                    @endcan
                    
                    @if(auth()->check() && !auth()->user()->hasPermissionTo('access admin dashboard') && auth()->user()->hasAnyPermission(['manage orders', 'view all orders']))
                    <a href="{{ route('admin.dashboard') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        {{ __('Orders') }}
                    </a>
                    @endif
                    @endauth
                    
                    <a href="{{ route('inventory.catalog') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('inventory.catalog') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        {{ __('Catalog') }}
                    </a>
                    
                    <a href="{{ route('contact') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('contact') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        {{ __('Contact Us') }}
                    </a>
                    
                    @guest
                    <a href="{{ route('login') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors text-white hover:bg-red-800/60 border border-red-400">
                        {{ __('Login') }}
                    </a>
                    @endguest
                    
                    @auth
                    @can('add to cart')
                    <a href="{{ route('customer.cart') }}" wire:navigate
                       class="px-3 py-2 text-sm font-medium rounded-md flex items-center transition-colors {{ request()->routeIs('customer.cart') ? 'bg-red-800 text-white' : 'text-white hover:bg-red-800/60' }}">
                        <span>{{ __('Cart') }}</span>
                        <livewire:cart.cart-counter location="desktop" :showTotal="true" />
                    </a>
                    @endcan
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-2">
                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-red-100 text-sm font-medium rounded-md text-white bg-red-800 hover:bg-red-900 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name" class="max-w-[150px] truncate"></div>

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
                        
                        @if(auth()->check() && auth()->user()->hasAnyPermission(['view netsuite sales data', 'view own orders']))
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
                @endauth
            </div>

            <!-- Mobile Cart Icon and Hamburger -->
            <div class="flex items-center sm:hidden space-x-2">
                @can('add to cart')
                <a href="{{ route('customer.cart') }}" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-red-800 focus:outline-none focus:bg-red-800 transition duration-150 ease-in-out" wire:navigate>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <livewire:cart.cart-counter location="mobile-icon" :showTotal="true" />
                    </div>
                </a>
                @endcan
                
                <!-- Hamburger -->
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-white transition duration-150 ease-in-out">
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
         class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-red-700 to-red-800 transform transition duration-300 ease-in-out sm:hidden"
         x-cloak>
        <div class="flex justify-between items-center p-4 border-b border-red-900">
            <div class="flex items-center">
                <a href="{{ url('/') }}" wire:navigate class="flex items-center">
                    <div class="flex items-center justify-center bg-white rounded-md p-1.5 shadow-sm">
                        <x-application-logo class="block h-8 w-auto" />
                    </div>
                    <span class="ml-2 text-sm font-semibold text-white">A&E Wholesale</span>
                </a>
            </div>
            <button @click="open = false" class="text-white hover:text-red-200">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="pt-2 pb-3 space-y-0.5 overflow-y-auto max-h-screen">
            <!-- Navigation menu starts here -->
            
            <a href="{{ route('dashboard') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                {{ __('Dashboard') }}
            </a>
            
            @can('access admin dashboard')
            <a href="{{ route('admin.dashboard') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                {{ __('Admin Dashboard') }}
            </a>
            @endcan
            
            @can('sync netsuite sales data')
            <a href="{{ route('sales.analytics') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('sales.analytics') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                {{ __('Sales Analytics') }}
            </a>
            @endcan
            
            @if(auth()->check() && !auth()->user()->hasPermissionTo('access admin dashboard') && auth()->user()->hasAnyPermission(['manage orders', 'view all orders']))
            <a href="{{ route('admin.dashboard') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                {{ __('Order Management') }}
            </a>
            @endif
            
            <a href="{{ route('inventory.catalog') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('inventory.catalog') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                {{ __('Product Catalog') }}
            </a>
            
            <a href="{{ route('contact') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('contact') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                {{ __('Contact Us') }}
            </a>
            
            @can('add to cart')
            <a href="{{ route('customer.cart') }}" wire:navigate @click="open = false"
               class="block px-4 py-2 text-base font-medium transition-colors flex items-center justify-between {{ request()->routeIs('customer.cart') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                <span>{{ __('Cart') }}</span>
                <livewire:cart.cart-counter location="mobile" :showTotal="true" />
            </a>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-red-900">
            @if(auth()->check())
            <div class="px-4">
                <div class="font-medium text-base text-white" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-red-200">{{ auth()->user()->email }}</div>
            </div>
            @endif

            <div class="mt-3 space-y-0.5">
                <a href="{{ route('profile') }}" wire:navigate @click="open = false"
                   class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('profile') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                    {{ __('Profile') }}
                </a>
                
                @can('view own orders')
                <a href="{{ route('customer.orders') }}" wire:navigate @click="open = false"
                   class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('customer.orders') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                    {{ __('My Web Orders') }}
                </a>
                @endcan
                
                @if(auth()->check() && auth()->user()->hasAnyPermission(['view netsuite sales data', 'view own orders']))
                <a href="{{ route('sales') }}" wire:navigate @click="open = false"
                   class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('sales') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                    {{ __('Sales History') }}
                </a>
                @endif
                
                @can('sync netsuite sales data')
                <a href="{{ route('sales.analytics') }}" wire:navigate @click="open = false"
                   class="block px-4 py-2 text-base font-medium transition-colors {{ request()->routeIs('sales.analytics') ? 'bg-red-900 text-white' : 'text-white hover:bg-red-900/70' }}">
                    {{ __('Sales Analytics') }}
                </a>
                @endcan

                <!-- Authentication -->
                <div class="px-4 py-2">
                    <button wire:click="logout" @click="open = false" 
                            class="w-full text-center px-3 py-2 rounded-md text-sm font-medium bg-white text-red-700 hover:bg-red-50 border border-red-100 shadow-sm">
                        {{ __('Log Out') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Backdrop when menu is open -->
    <div x-show="open" 
         @click="open = false"
         class="fixed inset-0 bg-gray-900 bg-opacity-80 z-40 transition-opacity sm:hidden" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
    </div>
</nav>
