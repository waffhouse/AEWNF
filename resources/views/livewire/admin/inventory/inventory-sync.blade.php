<div>
    <div class="flex justify-between items-center mb-3">
        <div class="flex-1"></div>
    </div>
    
    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold flex items-center text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Inventory Synchronization
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manually run the NetSuite inventory synchronization process. This process retrieves inventory data from NetSuite 
                    and updates the local database to ensure product information is current.
                </p>
            </div>
            
            @if($lastSyncTime)
            <div class="bg-amber-100 p-4 rounded-md border border-amber-200">
                <div class="flex flex-col space-y-2">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-amber-800">Last sync completed at: <span class="font-semibold">@formatdate($lastSyncTime)</span></p>
                        </div>
                    </div>
                    
                    @if($lastSyncStats)
                    <div class="mt-2 pt-3 border-t border-amber-200">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-sm font-medium text-amber-900">Current Inventory Summary</h4>
                            <span class="text-xs text-amber-700">Last updated {{ $lastSyncStats['time_since_sync'] }}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <div class="bg-white p-3 rounded-md shadow-sm border border-amber-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['total']) }}</div>
                                <div class="text-xs text-gray-600">Total Items</div>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm border border-amber-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['in_stock_items']) }}</div>
                                <div class="text-xs text-gray-600">In Stock</div>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm border border-amber-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['out_of_stock_items']) }}</div>
                                <div class="text-xs text-gray-600">Out of Stock</div>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm border border-amber-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['florida_items']) }}</div>
                                <div class="text-xs text-gray-600">Florida Items</div>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm border border-amber-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['georgia_items']) }}</div>
                                <div class="text-xs text-gray-600">Georgia Items</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <div class="flex justify-start">
                <button 
                    wire:click="runInventorySync"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-50"
                    {{ $syncRunning ? 'disabled' : '' }}
                >
                    <span wire:loading.remove wire:target="runInventorySync">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Run Inventory Sync
                    </span>
                    <span wire:loading wire:target="runInventorySync" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Syncing...
                    </span>
                </button>
            </div>
            
            @if($syncResults)
            <div class="mt-6">
                <h4 class="font-medium text-gray-900">Sync Results</h4>
                
                @if(isset($syncResults['error']))
                <div class="mt-2 bg-red-50 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Sync Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ $syncResults['error'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-2 overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $syncResults['total'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $syncResults['created'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $syncResults['updated'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ $syncResults['failed'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $syncResults['deleted'] ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $syncResults['duration'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif
            
            <div class="bg-gray-50 p-4 rounded-md mt-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-800">About Inventory Sync</h3>
                        <div class="mt-2 text-sm text-gray-700">
                            <p>The inventory sync process:</p>
                            <ul class="list-disc pl-5 space-y-1 mt-2">
                                <li>Retrieves all current inventory data from NetSuite</li>
                                <li>Creates new inventory items that don't exist locally</li>
                                <li>Updates existing inventory items with latest information</li>
                                <li>Removes items that no longer exist in NetSuite</li>
                                <li>Updates pricing information for all items</li>
                            </ul>
                            <p class="mt-2">The process is automatically scheduled to run hourly, but you can manually run it here as needed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages removed from here - now using the global notification component in dashboard.blade.php -->
</div>