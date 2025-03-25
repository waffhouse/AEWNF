<div>
    <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold flex items-center text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Sales Data Synchronization
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Sync sales transaction data from NetSuite. This will fetch the most recent invoices and credit memos.
                </p>
            </div>
            
            @if($lastSyncTime)
            <div class="bg-red-50 p-4 rounded-md border border-red-200">
                <div class="flex flex-col space-y-2">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-red-800">Last sync completed at: <span class="font-semibold">{{ \Carbon\Carbon::parse($lastSyncTime)->format('M d, Y h:i A') }}</span></p>
                        </div>
                    </div>
                    
                    @if($lastSyncStats)
                    <div class="mt-2 pt-3 border-t border-red-200">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-sm font-medium text-red-900">Current Sales Summary</h4>
                            <span class="text-xs text-red-700">Last updated {{ $lastSyncStats['time_since_sync'] }}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="bg-white p-3 rounded-md shadow-sm border border-red-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['total_sales']) }}</div>
                                <div class="text-xs text-gray-600">Total Transactions</div>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm border border-red-200">
                                <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($lastSyncStats['total_items']) }}</div>
                                <div class="text-xs text-gray-600">Total Line Items</div>
                            </div>
                            
                            @if(isset($lastSyncStats['type_stats']) && count($lastSyncStats['type_stats']) > 0)
                                @foreach($lastSyncStats['type_stats'] as $type => $stats)
                                <div class="bg-white p-3 rounded-md shadow-sm border border-red-200">
                                    <div class="text-xl lg:text-2xl font-bold text-gray-800 truncate">{{ number_format($stats['count']) }}</div>
                                    <div class="text-xs text-gray-600">{{ $type }}</div>
                                    <div class="text-xs text-red-600">${{ number_format($stats['total_amount'], 2) }}</div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="pageSize" class="block text-sm font-medium text-gray-700">Items Per Page</label>
                    <input type="number" 
                        id="pageSize" 
                        wire:model.defer="options.pageSize" 
                        min="1" 
                        max="1000"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Maximum 1000</p>
                </div>
                
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Transaction Date (Optional)</label>
                    <input type="date" 
                        id="date" 
                        wire:model.defer="options.date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Filter by specific date</p>
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button 
                    type="button"
                    wire:click="syncSales" 
                    wire:loading.attr="disabled"
                    wire:target="syncSales"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <span wire:loading.remove wire:target="syncSales">Sync Sales Data</span>
                    <span wire:loading wire:target="syncSales">Syncing...</span>
                </button>
                
                <button 
                    type="button"
                    onclick="if(confirm('WARNING: Are you sure you want to delete ALL sales data? This action cannot be undone.')) { Livewire.dispatch('clearData') }"
                    wire:loading.attr="disabled"
                    wire:target="directClearData"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <span wire:loading.remove wire:target="directClearData">Clear All Sales Data</span>
                    <span wire:loading wire:target="directClearData">Clearing...</span>
                </button>
            </div>
            
            @if($error)
                <div class="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                    <h4 class="text-sm font-medium">Error Processing Sales Data</h4>
                    <p class="text-sm mt-1">{{ $error }}</p>
                </div>
            @endif
            
            @if($results)
                <div class="mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                    <h4 class="text-sm font-medium">Sync Results</h4>
                    <dl class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2">
                        <div>
                            <dt class="text-xs text-green-700">Total Transactions</dt>
                            <dd class="font-medium">{{ $results['total'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-green-700">Duration</dt>
                            <dd class="font-medium">{{ $results['duration'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-green-700">Created</dt>
                            <dd class="font-medium">{{ $results['created'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-green-700">Updated</dt>
                            <dd class="font-medium">{{ $results['updated'] }}</dd>
                        </div>
                        @if(isset($results['deleted']) && $results['deleted'] > 0)
                        <div>
                            <dt class="text-xs text-green-700">Deleted</dt>
                            <dd class="font-medium">{{ $results['deleted'] }}</dd>
                        </div>
                        @endif
                        @if(isset($results['items_deleted']) && $results['items_deleted'] > 0)
                        <div>
                            <dt class="text-xs text-green-700">Items Deleted</dt>
                            <dd class="font-medium">{{ $results['items_deleted'] }}</dd>
                        </div>
                        @endif
                        @if(isset($results['netsuite_pages']))
                        <div>
                            <dt class="text-xs text-green-700">NetSuite Pages</dt>
                            <dd class="font-medium">{{ $results['netsuite_pages'] }}</dd>
                        </div>
                        @endif
                        @if(isset($results['netsuite_processed']))
                        <div>
                            <dt class="text-xs text-green-700">NetSuite Records</dt>
                            <dd class="font-medium">{{ $results['netsuite_processed'] }}</dd>
                        </div>
                        @endif
                        @if(isset($results['failed']) && $results['failed'] > 0)
                            <div>
                                <dt class="text-xs text-red-700">Failed</dt>
                                <dd class="font-medium text-red-700">{{ $results['failed'] }}</dd>
                            </div>
                        @endif
                    </dl>
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
                        <h3 class="text-sm font-medium text-gray-800">About Sales Data Sync</h3>
                        <div class="mt-2 text-sm text-gray-700">
                            <p>The sales data sync process:</p>
                            <ul class="list-disc pl-5 space-y-1 mt-2">
                                <li>Retrieves all current sales data from NetSuite</li>
                                <li>Creates new sales records that don't exist locally</li>
                                <li>Updates existing sales records with latest information</li>
                                <li>Synchronizes all line items for each transaction</li>
                            </ul>
                            <p class="mt-2">The sync can be filtered by date if needed. For full history, leave the date field empty.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>