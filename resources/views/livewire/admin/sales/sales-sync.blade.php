<div>
    <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Sync Sales Data from NetSuite</h3>
        <p class="mt-1 text-sm text-gray-500">
            Sync sales transaction data from NetSuite. This will fetch the most recent invoices and credit memos.
        </p>
        
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="pageIndex" class="block text-sm font-medium text-gray-700">Page Number</label>
                <input type="number" 
                    id="pageIndex" 
                    wire:model="options.pageIndex" 
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Start with page 0</p>
            </div>
            
            <div>
                <label for="pageSize" class="block text-sm font-medium text-gray-700">Page Size</label>
                <input type="number" 
                    id="pageSize" 
                    wire:model="options.pageSize" 
                    min="1" 
                    max="1000"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Maximum 1000</p>
            </div>
            
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Transaction Date (Optional)</label>
                <input type="date" 
                    id="date" 
                    wire:model="options.date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Filter by specific date</p>
            </div>
        </div>
        
        <div class="mt-4">
            <button 
                type="button"
                wire:click="syncSales" 
                wire:loading.attr="disabled"
                wire:target="syncSales"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                <span wire:loading.remove wire:target="syncSales">Sync Sales Data</span>
                <span wire:loading wire:target="syncSales">Syncing...</span>
            </button>
        </div>
        
        @if($error)
            <div class="mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                <h4 class="text-sm font-medium">Error Syncing Sales Data</h4>
                <p class="text-sm mt-1">{{ $error }}</p>
            </div>
        @endif
        
        @if($results)
            <div class="mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                <h4 class="text-sm font-medium">Sync Results</h4>
                <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2">
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
                    @if(isset($results['failed']) && $results['failed'] > 0)
                        <div>
                            <dt class="text-xs text-red-700">Failed</dt>
                            <dd class="font-medium text-red-700">{{ $results['failed'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </div>
</div>