<div>
    <div class="p-6 bg-white rounded-lg shadow">
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900">Customer Data Synchronization</h3>
            <p class="mt-1 text-sm text-gray-600">
                Sync customer data from NetSuite to keep your local database up-to-date.
            </p>
        </div>
        
        <!-- Last Sync Info -->
        <div class="mb-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
            <h4 class="text-md font-medium text-gray-700 mb-2">Sync Status</h4>
            
            @if ($lastSyncTime)
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-blue-700">
                        Last sync: <span class="font-medium">{{ $lastSyncTime }}</span>
                    </span>
                </div>
                <div class="flex items-center mb-2 ml-7">
                    <span class="text-sm text-gray-600">
                        {{ $lastSyncStats['time_since_sync'] }}
                    </span>
                </div>
            @else
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm text-amber-600 font-medium">
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
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-indigo-700">Total Customers</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-indigo-900 mt-2">{{ $lastSyncStats['total'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-blue-700">Florida Customers</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-blue-900 mt-2">{{ $lastSyncStats['florida_customers'] }}</p>
                        <p class="text-sm text-blue-600 mt-1">{{ number_format($lastSyncStats['florida_customers'] / $lastSyncStats['total'] * 100, 1) }}% of total</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-green-700">Georgia Customers</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-green-900 mt-2">{{ $lastSyncStats['georgia_customers'] }}</p>
                        <p class="text-sm text-green-600 mt-1">{{ number_format($lastSyncStats['georgia_customers'] / $lastSyncStats['total'] * 100, 1) }}% of total</p>
                    </div>
                </div>
                
                <!-- Top Counties Distribution if available -->
                @if (!empty($lastSyncStats['top_counties']))
                    <div class="mt-6">
                        <div class="flex items-center mb-2">
                            <h4 class="text-md font-medium text-gray-700">Top Counties by Customer Count</h4>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($lastSyncStats['top_counties'] as $county => $count)
                                <div class="bg-amber-50 p-4 rounded-lg border border-amber-100 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-amber-700">{{ $county }} County</p>
                                        <span class="text-xs bg-amber-200 text-amber-800 px-2 py-1 rounded-full">
                                            {{ number_format($count / $lastSyncStats['total'] * 100, 1) }}%
                                        </span>
                                    </div>
                                    <p class="text-2xl font-bold text-amber-800 mt-2">{{ $count }}</p>
                                    <p class="text-sm text-amber-600 mt-1">{{ Str::plural('customer', $count) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
        
        <!-- Sync Button -->
        <div class="mt-6 bg-gray-50 p-5 rounded-lg border border-gray-200 flex justify-between items-center">
            <div>
                <h4 class="text-md font-medium text-gray-700 mb-1">Sync Now</h4>
                <p class="text-sm text-gray-500">Fetch the latest customer data from NetSuite</p>
            </div>
            <button 
                wire:click="runCustomerSync"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
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
            <div class="mt-6 p-5 border rounded-lg bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-md font-medium text-gray-700">Last Sync Results</h4>
                    
                    @if (!empty($syncResults['errors']))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ count($syncResults['errors']) }} error(s)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Completed successfully
                        </span>
                    @endif
                </div>
                
                <!-- Errors Section -->
                @if (!empty($syncResults['errors']) && count($syncResults['errors']) <= 5)
                    <div class="mb-4 bg-red-50 p-3 rounded border border-red-100 text-sm text-red-800">
                        <div class="font-medium mb-1">Error Details:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($syncResults['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Results Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-700">Total Processed</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $syncResults['total'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">records from NetSuite</p>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-green-100">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-green-700">New Records</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-green-800 mt-2">{{ $syncResults['created'] }}</p>
                        <p class="text-xs text-green-600 mt-1">new customers created</p>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-100">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-blue-700">Updated</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-blue-800 mt-2">{{ $syncResults['updated'] }}</p>
                        <p class="text-xs text-blue-600 mt-1">records updated</p>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-red-100">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-red-700">Failed/Skipped</p>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-red-800 mt-2">{{ $syncResults['failed'] + $syncResults['skipped'] }}</p>
                        <p class="text-xs text-red-600 mt-1">records with issues</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>