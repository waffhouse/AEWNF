<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Catalog specific styles -->
        <style>
            .pb-sticky-filter {
                padding-bottom: 3.5rem !important;
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col bg-gray-100">
            <!-- Fixed Header -->
            <header class="fixed top-0 inset-x-0 z-40">
                @auth
                <!-- Logged-in navigation with red gradient background -->
                <div class="bg-gradient-to-r from-red-700 to-red-600 shadow-md">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">
                            <!-- Logo -->
                            <div class="flex items-center">
                                <a href="{{ route('dashboard') }}" class="flex items-center">
                                    <div class="flex items-center justify-center bg-white rounded-md p-2 shadow-sm">
                                        <img src="{{ asset('images/logo.png') }}" alt="A&E Wholesale" class="h-8 w-auto">
                                    </div>
                                    <div class="ml-4 pl-4 border-l border-red-400">
                                        <h1 class="text-base sm:text-lg font-semibold text-white hidden sm:inline-block">A&E Wholesale of North Florida</h1>
                                        <p class="text-xs text-red-100 hidden sm:inline-block">Customer Dashboard</p>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Nav Links - Desktop -->
                            <div class="hidden md:flex items-center space-x-2">
                                <a href="{{ route('dashboard') }}" class="text-white hover:bg-red-800 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-red-800' : '' }}">
                                    Dashboard
                                </a>
                                
                                @can('access admin dashboard')
                                <a href="{{ route('admin.dashboard') }}" class="text-white hover:bg-red-800 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-red-800' : '' }}">
                                    Admin
                                </a>
                                @endcan
                                
                                <a href="{{ route('inventory.catalog') }}" class="text-white hover:bg-red-800 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('inventory.catalog') ? 'bg-red-800' : '' }}">
                                    Catalog
                                </a>
                                
                                <a href="{{ route('customer.cart') }}" class="text-white hover:bg-red-800 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('customer.cart') ? 'bg-red-800' : '' }}">
                                    Cart
                                </a>
                                
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="bg-white text-red-700 hover:bg-red-50 px-3 py-2 text-sm font-medium rounded-md border border-red-100 shadow-sm">
                                    Log Out
                                </a>
                                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                            
                            <!-- Mobile menu button -->
                            <div class="md:hidden">
                                <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Mobile menu -->
                        <div class="hidden md:hidden" id="mobile-menu">
                            <div class="px-2 pt-2 pb-3 space-y-1 border-t border-red-800">
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-red-800 {{ request()->routeIs('dashboard') ? 'bg-red-800' : '' }}">
                                    Dashboard
                                </a>
                                
                                @can('access admin dashboard')
                                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-red-800 {{ request()->routeIs('admin.dashboard') ? 'bg-red-800' : '' }}">
                                    Admin
                                </a>
                                @endcan
                                
                                <a href="{{ route('inventory.catalog') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-red-800 {{ request()->routeIs('inventory.catalog') ? 'bg-red-800' : '' }}">
                                    Catalog
                                </a>
                                
                                <a href="{{ route('customer.cart') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-red-800 {{ request()->routeIs('customer.cart') ? 'bg-red-800' : '' }}">
                                    Cart
                                </a>
                                
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-3 py-2 rounded-md text-base font-medium bg-white text-red-700 hover:bg-red-50 mt-2 border border-red-100 shadow-sm">
                                    Log Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <!-- Public header design - full width red gradient -->
                <div class="bg-gradient-to-r from-red-700 to-red-600 shadow-md">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <!-- Logo & Catalog Title -->
                            <div class="flex items-center justify-center sm:justify-start">
                                <a href="/" class="flex items-center">
                                    <div class="flex items-center justify-center bg-white rounded-md p-2 shadow-sm">
                                        <img src="{{ asset('images/logo.png') }}" alt="A&E Wholesale" class="h-9 w-auto">
                                    </div>
                                    <div class="ml-4 pl-4 border-l border-red-400">
                                        <h1 class="text-lg sm:text-xl font-semibold text-white">A&E Wholesale of North Florida</h1>
                                        <p class="text-xs sm:text-sm text-red-100">Browse our product catalog</p>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Login button -->
                            <div class="mt-3 sm:mt-0 text-center sm:text-right">
                                <a href="{{ route('login') }}" class="px-5 py-2 bg-white text-red-700 rounded-md text-sm font-medium hover:bg-red-50 inline-block border border-red-100 shadow-sm">
                                    Log in for Pricing
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth
            </header>

            <!-- Spacer to account for fixed header - different heights for auth/guest -->
            <div class="@auth pt-16 md:pt-16 @else pt-20 sm:pt-16 @endauth"></div>

            <div class="flex-1 flex flex-col items-center">
                <div class="w-full max-w-7xl bg-white shadow-md overflow-hidden">
                    {{ $slot }}
                    
                    <footer class="px-8 py-6 bg-gradient-to-r from-red-600 to-red-700 border-t border-red-800 text-white">
                        <div class="flex flex-col justify-center items-center">
                            <div class="mb-4 text-center">
                                <p class="text-sm">&copy; {{ date('Y') }} A&E Wholesale of North Florida. All rights reserved.</p>
                            </div>
                            <div class="flex space-x-6">
                                <a href="{{ route('login') }}" class="text-sm text-white hover:text-red-100 font-medium">Login for pricing</a>
                                <a href="/" class="text-sm text-white hover:text-red-100 font-medium">Home</a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>