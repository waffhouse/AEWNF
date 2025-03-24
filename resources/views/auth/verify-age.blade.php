<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'A&E Wholesale') }} - Age Verification</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 relative overflow-hidden">
        <!-- Background Overlay Pattern -->
        <div class="absolute inset-0 bg-gray-900 opacity-50 z-0"></div>
        
        <!-- Age Verification Card -->
        <div class="w-full max-w-md relative z-10">
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden transform transition-all">
                <!-- Header with Brand -->
                <div class="bg-gray-900 px-6 py-4 flex justify-center">
                    <img src="{{ asset('images/AEWBlack.png') }}" alt="A&E Wholesale" class="h-16 object-contain">
                </div>
                
                <div class="px-8 py-8">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">
                            Age Verification Required
                        </h2>
                        <div class="w-16 h-1 bg-red-600 mx-auto mb-4"></div>
                        <p class="text-gray-600 mb-6">
                            This website contains age-restricted products.<br>To continue, please verify that you are 21 years of age or older.
                        </p>
                    </div>

                    @if (session('error'))
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verify.age.submit') }}">
                        @csrf

                        <div class="flex items-center justify-center my-6 border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="confirm_age" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500 focus:ring-opacity-50" required />
                                <span class="ml-3 text-gray-700 font-medium">I confirm that I am at least 21 years old</span>
                            </label>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full py-3 px-4 bg-red-600 text-white font-medium rounded-lg shadow-md border-0 hover:bg-red-700 transition duration-150 ease-in-out">
                                Enter Site
                            </button>
                        </div>
                        
                        <p class="mt-6 text-xs text-center text-gray-500">
                            By entering, you accept our Terms of Service and Privacy Policy.<br>
                            We do not sell alcohol to anyone under 21 years of age.
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Legal Footer -->
        <div class="mt-8 text-center text-white text-xs relative z-10">
            <p>&copy; {{ date('Y') }} A&E Wholesale of North Florida. All rights reserved.</p>
        </div>
    </div>
</body>
</html>