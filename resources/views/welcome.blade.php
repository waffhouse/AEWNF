<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ env('COMPANY_NAME') }} - Your trusted supplier of wholesale products.">

        <title>{{ env('COMPANY_NAME', config('app.name')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 font-sans">
        <!-- Navigation -->
        <livewire:layout.navigation />
        
        <div class="min-h-screen flex flex-col pt-16">

            <!-- Hero Section -->
            <div class="relative py-12 sm:py-16 lg:py-20 bg-gradient-to-br from-red-50 to-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center mb-8">
                        <img class="w-auto h-24 md:h-32 object-contain" src="{{ asset('images/logo.png') }}" alt="A&E Wholesale Logo">
                        <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl text-center mt-6">
                            <span class="block">{{ env('COMPANY_NAME_SHORT') }} Wholesale</span>
                            <span class="block">of</span>
                            <span class="block text-red-600">North Florida</span>
                        </h1>
                    </div>
                    
                    <div class="text-center">
                        <p class="mx-auto max-w-2xl text-lg text-gray-600">
                            Your trusted supplier of quality wholesale products in north Florida and south Georgia. Browse our extensive catalog and place orders online.
                        </p>
                        <div class="mt-10 flex flex-col sm:flex-row justify-center sm:space-x-4 space-y-4 sm:space-y-0">
                            <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                View Catalog
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                Contact Us
                            </a>
                            @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                Customer Login
                            </a>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="py-12 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="lg:text-center">
                        <h2 class="text-base text-red-600 font-semibold tracking-wide uppercase">Features</h2>
                        <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            Everything you need, all in one place
                        </p>
                        <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                            Our online ordering platform makes it easy to browse products, check availability, and place orders.
                        </p>
                    </div>

                    <div class="mt-10">
                        <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                            <!-- Feature 1 -->
                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                </div>
                                <div class="ml-16">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Online Catalog</h3>
                                    <p class="mt-2 text-base text-gray-500">
                                        Browse our complete product catalog with real-time inventory and pricing information.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 2 -->
                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                    </svg>
                                </div>
                                <div class="ml-16">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Simple Ordering</h3>
                                    <p class="mt-2 text-base text-gray-500">
                                        Easily add products to your cart and place orders with our streamlined checkout process.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 3 -->
                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="ml-16">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Real-time Updates</h3>
                                    <p class="mt-2 text-base text-gray-500">
                                        Get instant access to product availability, pricing, and order status information.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 4 -->
                            <div class="relative">
                                <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-16">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order History</h3>
                                    <p class="mt-2 text-base text-gray-500">
                                        View your complete order history and easily reorder your favorite products.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-red-700">
                <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
                    <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        <span class="block">Ready to get started?</span>
                        <span class="block text-red-100">Browse our catalog today.</span>
                    </h2>
                    <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                        <div class="inline-flex rounded-md shadow">
                            <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-red-600 bg-white hover:bg-red-50 transition duration-150 ease-in-out">
                                View Catalog
                            </a>
                        </div>
<!-- Registration is disabled - users can only be created by admins -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-white">
                <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                    <!-- Divider line -->
                    <div class="border-t border-gray-200 mb-8"></div>
                    
                    <div class="flex flex-col md:flex-row justify-between">
                        <!-- Left column - Company info -->
                        <div class="flex flex-col items-center md:items-start">
                            <img class="h-10 mb-6" src="{{ asset('images/AEWBlack.png') }}" alt="{{ env('COMPANY_NAME') }} Logo">
                            
                            <div class="text-center md:text-left text-sm text-gray-600">
                                <p class="font-medium">{{ env('COMPANY_NAME') }}</p>
                                <p class="mt-1">
                                    {{ env('COMPANY_STREET') }}, {{ env('COMPANY_STREET_2') }}<br>
                                    {{ env('COMPANY_CITY') }}, {{ env('COMPANY_STATE') }} {{ env('COMPANY_ZIP') }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Right column - Contact and license info -->
                        <div class="flex flex-col text-center md:text-right mt-8 md:mt-0">
                            <div class="text-sm text-gray-600">
                                <p>Phone: <a href="tel:{{ preg_replace('/[^0-9]/', '', env('COMPANY_PHONE')) }}" class="hover:text-red-600">{{ env('COMPANY_PHONE') }}</a></p>
                                <p>Email: <a href="mailto:{{ env('COMPANY_EMAIL') }}" class="hover:text-red-600">{{ env('COMPANY_EMAIL') }}</a></p>
                                <p class="mt-1">
                                    <a href="{{ env('COMPANY_WEBSITE_URL1') }}" target="_blank" class="hover:text-red-600 transition-colors">{{ str_replace(['https://', 'http://'], '', env('COMPANY_WEBSITE_URL1')) }}</a>
                                </p>
                            </div>
                            
                            <div class="mt-6 text-xs text-gray-500">
                                <p>Licensed in Florida: WDE{{ env('COMPANY_CWD_LICENSE') }}, WDE{{ env('COMPANY_TWD_LICENSE') }}</p>
                                <p>Georgia: {{ env('COMPANY_GA_LICENSE') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Copyright -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <p class="text-center text-xs text-gray-500">
                            &copy; {{ date('Y') }} {{ env('COMPANY_NAME') }}. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>