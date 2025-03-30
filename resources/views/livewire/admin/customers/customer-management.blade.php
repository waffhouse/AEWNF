<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-800">Customer Management</h2>
    </div>
    
    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
            <li class="mr-2">
                <button 
                    wire:click="showListTab" 
                    class="inline-flex items-center p-4 rounded-t-lg {{ $activeTab === 'list' ? 'text-red-600 border-b-2 border-red-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Customer List
                </button>
            </li>
            <li class="mr-2">
                <button 
                    wire:click="showSyncTab"
                    class="inline-flex items-center p-4 rounded-t-lg {{ $activeTab === 'sync' ? 'text-red-600 border-b-2 border-red-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Customers
                </button>
            </li>
        </ul>
    </div>
    
    <!-- Tab Content -->
    <div>
        @if ($activeTab === 'list')
            <livewire:admin.customers.customer-list />
        @elseif ($activeTab === 'sync')
            <livewire:admin.customers.customer-sync />
        @endif
    </div>
</div>