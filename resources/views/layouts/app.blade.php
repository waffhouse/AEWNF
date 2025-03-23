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

            <!-- Flash Messages - Smooth stacked notification component with newest on top -->
            <div 
                x-data="{ 
                    notifications: [],
                    add(message, type = 'success') {
                        // Create a unique ID for this notification
                        const id = Date.now() + Math.floor(Math.random() * 1000);
                        
                        // Add the new notification to the beginning of the array (newest on top)
                        this.notifications.unshift({
                            id: id,
                            message: message,
                            type: type,
                            show: true
                        });
                        
                        // Set a timeout to remove this notification
                        setTimeout(() => {
                            this.dismiss(id);
                        }, 4000);
                    },
                    dismiss(id) {
                        // Find notification and mark it for animation-out
                        const index = this.notifications.findIndex(n => n.id === id);
                        if (index !== -1) {
                            this.notifications[index].show = false;
                            // Actually remove after animation completes
                            setTimeout(() => {
                                this.remove(id);
                            }, 300);
                        }
                    },
                    remove(id) {
                        this.notifications = this.notifications.filter(notification => notification.id !== id);
                    }
                }"
                x-init="
                    window.addEventListener('livewire:initialized', () => {
                        Livewire.on('notification', (data) => {
                            add(data.message, data.type || 'success');
                        });
                    });
                "
                class="fixed top-20 right-4 z-50 flex flex-col space-y-2 max-h-[80vh] overflow-hidden pointer-events-none"
            >
                <template x-for="notification in notifications" :key="notification.id">
                    <div 
                        x-show="notification.show"
                        x-transition:enter="transform transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-[-100%] translate-x-4"
                        x-transition:enter-end="opacity-100 translate-y-0 translate-x-0"
                        x-transition:leave="transform transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-0 scale-95"
                        class="w-72 p-4 rounded shadow-lg pointer-events-auto relative transition-all duration-300 ease-in-out"
                        :class="notification.type === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 
                                (notification.type === 'warning' ? 'bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500' : 
                                'bg-red-100 text-red-800 border-l-4 border-red-500')"
                    >
                        <div class="flex items-start justify-between">
                            <span x-text="notification.message" class="flex-1 pr-2"></span>
                            <button 
                                @click="dismiss(notification.id)" 
                                class="text-gray-500 hover:text-gray-700 ml-1 flex-shrink-0 focus:outline-none"
                                aria-label="Close notification"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
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
