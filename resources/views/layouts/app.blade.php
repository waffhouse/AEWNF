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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
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

            <!-- Flash Messages - Global notification component -->
            <div 
                x-data="{ show: false, message: '', type: '' }"
                x-show="show"
                x-init="
                    window.addEventListener('livewire:initialized', () => {
                        Livewire.on('notification', (data) => {
                            show = true;
                            message = data.message;
                            type = data.type || 'success';
                            setTimeout(() => show = false, 3000);
                        });
                    });
                "
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="fixed top-20 right-4 z-50 w-72 p-4 rounded shadow-lg"
                :class="type === 'success' ? 'bg-green-100 text-green-800' : (type === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')"
                style="display: none;"
            >
                <div class="flex items-center justify-between">
                    <span x-text="message"></span>
                    <button 
                        @click="show = false" 
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

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
        </div>
    </body>
</html>
