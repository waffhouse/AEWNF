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
            <header class="fixed top-0 inset-x-0 z-40 bg-white shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @auth
                        <!-- Logged-in navigation -->
                        <div class="flex justify-between items-center h-16">
                            <!-- Logo -->
                            <div class="flex items-center">
                                <a href="{{ route('dashboard') }}" class="flex items-center">
                                    <img src="{{ asset('images/logo.png') }}" alt="A&E Wholesale" class="h-8 w-auto">
                                    <span class="ml-2 text-red-600 font-semibold hidden sm:inline-block">A&E Wholesale of North Florida</span>
                                </a>
                            </div>
                            
                            <!-- Nav Links - Desktop -->
                            <div class="hidden md:flex items-center space-x-4">
                                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-red-600' : '' }}">
                                    Dashboard
                                </a>
                                
                                @can('access admin dashboard')
                                <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'text-red-600' : '' }}">
                                    Admin
                                </a>
                                @endcan
                                
                                <a href="{{ route('inventory.catalog') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium {{ request()->routeIs('inventory.catalog') ? 'text-red-600' : '' }}">
                                    Catalog
                                </a>
                                
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                                    Log Out
                                </a>
                                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                            
                            <!-- Mobile menu button -->
                            <div class="md:hidden">
                                <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Mobile menu -->
                        <div class="hidden md:hidden" id="mobile-menu">
                            <div class="px-2 pt-2 pb-3 space-y-1">
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-gray-50' }}">
                                    Dashboard
                                </a>
                                
                                @can('access admin dashboard')
                                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-gray-50' }}">
                                    Admin
                                </a>
                                @endcan
                                
                                <a href="{{ route('inventory.catalog') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('inventory.catalog') ? 'bg-red-50 text-red-600' : 'text-gray-700 hover:bg-gray-50' }}">
                                    Catalog
                                </a>
                                
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">
                                    Log Out
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Public header design (more compact) -->
                        <div class="py-2 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <!-- Logo & Catalog Title -->
                            <div class="flex items-center justify-center sm:justify-start">
                                <a href="/" class="flex-shrink-0">
                                    <img src="{{ asset('images/logo.png') }}" alt="A&E Wholesale" class="h-10 w-auto">
                                </a>
                                <div class="ml-4 border-l pl-4">
                                    <h1 class="text-lg sm:text-xl font-semibold text-gray-800">A&E Wholesale of North Florida</h1>
                                    <p class="text-xs sm:text-sm text-gray-500">Browse our product catalog</p>
                                </div>
                            </div>
                            
                            <!-- Login button -->
                            <div class="mt-3 sm:mt-0 text-center sm:text-right">
                                <a href="{{ route('login') }}" class="px-5 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 inline-block">
                                    Log in for Pricing
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            <!-- Spacer to account for fixed header - different heights for auth/guest -->
            <div class="@auth pt-16 md:pt-16 @else pt-20 sm:pt-16 @endauth"></div>

            <div class="flex-1 flex flex-col items-center">
                <div class="w-full max-w-7xl bg-white shadow-md overflow-hidden">
                    {{ $slot }}
                    
                    <footer class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-col justify-center items-center">
                            <div class="mb-4 text-center">
                                <p class="text-sm text-gray-600">&copy; {{ date('Y') }} A&E Wholesale of North Florida. All rights reserved.</p>
                            </div>
                            <div class="flex space-x-6">
                                <a href="{{ route('login') }}" class="text-sm text-red-600 hover:text-red-800">Login for pricing</a>
                                <a href="/" class="text-sm text-blue-600 hover:text-blue-800">Home</a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>