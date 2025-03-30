<div>
    <div 
        x-data="{ show: @entangle('showModal') }"
        x-show="show" 
        class="fixed inset-0 z-50 overflow-y-auto" 
        x-cloak
    >
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="show = false"></div>

            <div class="relative bg-white rounded-lg max-w-3xl w-full mx-auto shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Customer Details</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" x-on:click="show = false">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-4 py-5 sm:p-6 max-h-[70vh] overflow-y-auto">
                    @if($customer)
                    <div class="space-y-6">
                        <!-- Customer Summary -->
                        <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $customer->company_name }}</h3>
                                <p class="text-sm text-gray-600">Customer ID: {{ $customer->entity_id }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if($customer->home_state)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $customer->home_state }}
                                    </span>
                                @endif
                                @if($customer->license_type)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $customer->license_type }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="border rounded-md overflow-hidden">
                            <div class="px-4 py-2 bg-gray-50 border-b">
                                <h4 class="font-medium text-gray-700">Contact Information</h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->email ?: 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->phone ?: 'Not provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Address</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($customer->shipping_address)
                                            {!! nl2br(e($customer->shipping_address)) !!}
                                        @else
                                            Not provided
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">County</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->county ?: 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- License Information -->
                        <div class="border rounded-md overflow-hidden">
                            <div class="px-4 py-2 bg-gray-50 border-b">
                                <h4 class="font-medium text-gray-700">License Information</h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">License Type</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->license_type ?: 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">License Number</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->license_number ?: 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Information -->
                        <div class="border rounded-md overflow-hidden">
                            <div class="px-4 py-2 bg-gray-50 border-b">
                                <h4 class="font-medium text-gray-700">Account Information</h4>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Terms</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->terms ?: 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Price Level</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->price_level ?: 'Default' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Last Updated -->
                        <div class="text-xs text-gray-500 text-right italic">
                            Last updated: {{ $customer->updated_at->diffForHumans() }}
                        </div>
                    </div>
                    @else
                    <div class="py-8 text-center text-gray-500">
                        <p>Loading customer details...</p>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 sm:px-6 flex justify-end">
                    <button type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" x-on:click="show = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>