<div>
    <div class="p-6 bg-white rounded-lg shadow">
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900">Customer Data Synchronization</h3>
            <p class="mt-1 text-sm text-gray-600">
                Sync customer data from NetSuite to keep your local database up-to-date.
            </p>
        </div>
        
        <!-- Last Sync Info -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-700 mb-2">Current Status</h4>
            
            @if ($lastSyncTime)
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-gray-600">
                        Last sync: <span class="font-medium">{{ $lastSyncTime }}</span>
                        ({{ $lastSyncStats['time_since_sync'] }})
                    </span>
                </div>
            @else
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm text-amber-600">
                        No customer data sync has been performed yet
                    </span>
                </div>
            @endif
        </div>
        
        <!-- Statistics -->
        @if ($lastSyncStats)
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-700 mb-2">Customer Statistics</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-gray-500">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lastSyncStats['total'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-blue-500">Florida Customers</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $lastSyncStats['florida_customers'] }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm font-medium text-green-500">Georgia Customers</p>
                        <p class="text-2xl font-bold text-green-900">{{ $lastSyncStats['georgia_customers'] }}</p>
                    </div>
                </div>
                
                <!-- License Type Distribution if available -->
                @if (!empty($lastSyncStats['license_types']))
                    <div class="mt-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2">License Types</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($lastSyncStats['license_types'] as $type => $count)
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-purple-500">{{ $type }}</p>
                                    <p class="text-xl font-bold text-purple-900">{{ $count }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
        
        <!-- Sync Button -->
        <div class="mt-6">
            <button 
                wire:click="runCustomerSync"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                @if($syncRunning) disabled @endif
            >
                <span wire:loading.remove wire:target="runCustomerSync">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Customer Data
                </span>
                <span wire:loading wire:target="runCustomerSync">
                    <svg class="animate-spin h-5 w-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>
        
        <!-- Recent Sync Results -->
        @if ($syncResults)
            <div class="mt-6 p-4 border rounded-lg bg-gray-50">
                <h4 class="text-md font-medium text-gray-700 mb-2">Last Sync Results</h4>
                
                @if (!empty($syncResults['errors']))
                    <div class="flex items-center text-red-600 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ count($syncResults['errors']) }} error(s) occurred during sync</span>
                    </div>
                    
                    @if (count($syncResults['errors']) <= 5)
                        <div class="mt-2 bg-red-50 p-3 rounded text-sm text-red-800">
                            <ul class="list-disc pl-5">
                                @foreach($syncResults['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <div class="flex items-center text-green-600 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Completed successfully</span>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <div class="bg-white p-3 rounded-lg shadow-sm border">
                        <p class="text-sm font-medium text-gray-500">Total Processed</p>
                        <p class="text-xl font-bold text-gray-900">{{ $syncResults['total'] }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border">
                        <p class="text-sm font-medium text-green-500">Created</p>
                        <p class="text-xl font-bold text-green-900">{{ $syncResults['created'] }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border">
                        <p class="text-sm font-medium text-blue-500">Updated</p>
                        <p class="text-xl font-bold text-blue-900">{{ $syncResults['updated'] }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm border">
                        <p class="text-sm font-medium text-red-500">Failed/Skipped</p>
                        <p class="text-xl font-bold text-red-900">{{ $syncResults['failed'] + $syncResults['skipped'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>