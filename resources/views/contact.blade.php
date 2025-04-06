<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Hero Banner with Red Gradient Background -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 sm:rounded-t-lg shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Contact Us</h2>
                        <p class="text-sm text-red-100 mt-1">We're here to help. Send us a message and we'll get back to you.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-b-lg">
                <div class="max-w-3xl mx-auto p-6 text-gray-900">

        <div class="overflow-hidden mb-10">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Get In Touch</h2>
                        <p class="text-gray-600 mb-6">Have a question or need assistance? Fill out the form and we'll get back to you as soon as possible.</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-gray-700">
                                    <p class="text-sm font-medium">Phone</p>
                                    <p class="mt-1"><a href="tel:{{ env('COMPANY_PHONE') }}" class="hover:text-red-600">{{ env('COMPANY_PHONE') }}</a></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-gray-700">
                                    <p class="text-sm font-medium">Email</p>
                                    <p class="mt-1"><a href="mailto:{{ env('COMPANY_EMAIL') }}" class="hover:text-red-600">{{ env('COMPANY_EMAIL') }}</a></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-gray-700">
                                    <p class="text-sm font-medium">Address</p>
                                    <p class="mt-1">
                                        {{ env('COMPANY_STREET') }}<br>
                                        @if(env('COMPANY_STREET_2'))
                                            {{ env('COMPANY_STREET_2') }}<br>
                                        @endif
                                        {{ env('COMPANY_CITY') }}, {{ env('COMPANY_STATE') }} {{ env('COMPANY_ZIP') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-gray-700">
                                    <p class="text-sm font-medium">Business Hours</p>
                                    <p class="mt-1">Monday - Friday: 8:00 AM - 4:00 PM<br>Saturday - Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        @if(session('success'))
                            <div class="rounded-md bg-green-50 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="rounded-md bg-red-50 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <div class="mt-1">
                                    <input type="text" name="name" id="name" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('name') }}" required>
                                </div>
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <div class="mt-1">
                                    <input type="email" name="email" id="email" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('email') }}" required>
                                </div>
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone (optional)</label>
                                <div class="mt-1">
                                    <input type="text" name="phone" id="phone" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('phone') }}">
                                </div>
                                @error('phone')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                <div class="mt-1">
                                    <textarea id="message" name="message" rows="4" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" required>{{ old('message') }}</textarea>
                                </div>
                                @error('message')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>