<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'A&E Wholesale of North Florida') }}</title>
        
        <!-- Favicon -->
        <link rel="icon" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Load CSS normally -->
        <link rel="stylesheet" href="{{ asset('build/assets/app-CVMN1sKz.css') }}">
        
        <!-- Load JS synchronously (without defer/async) -->
        <script src="{{ asset('build/assets/app-BaN6vmzo.js') }}"></script>
        
        <!-- Application specific styles -->
        <style>
            .pb-sticky-filter {
                padding-bottom: 3.5rem !important;
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Space to account for fixed header -->
            <div class="pt-16"></div>

            <!-- Notifications removed temporarily -->

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            
            <!-- Global Modal Components - Available on all pages using this layout -->
            <!-- Used for viewing order details across the dashboard and order history pages -->
            <livewire:modals.order-detail-modal />
            <!-- Used for viewing transaction details across the dashboard and sales history pages -->
            <livewire:modals.transaction-detail-modal />
            
            <!-- Scroll to Top Component -->
            <x-scroll-to-top />
        </div>
    </body>
</html>
