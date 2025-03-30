<div>
    <!-- Scope the whole component to isolate Alpine context -->
    <div x-data="{}" id="customer-management-component">
        <!-- Scroll to top button -->
        <x-scroll-to-top />
        
        <div class="space-y-6">
            <!-- Customer Header -->
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold flex items-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Customer Management
                </h2>
                
                <!-- Tab Navigation -->
                <div class="flex space-x-2">
                    <button
                        wire:click="showListTab"
                        class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium
                        {{ $activeTab === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="hidden sm:inline">Customer List</span>
                        <span class="sm:hidden">List</span>
                    </button>
                    
                    <button
                        wire:click="showSyncTab"
                        class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium
                        {{ $activeTab === 'sync' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="hidden sm:inline">Sync Customers</span>
                        <span class="sm:hidden">Sync</span>
                    </button>
                </div>
            </div>
            
            <!-- Tab Content -->
            <div>
                @if ($activeTab === 'list')
                    <livewire:admin.customers.customer-list />
                @elseif ($activeTab === 'sync')
                    <livewire:admin.customers.customer-sync />
                @endif
            </div>
            
            <!-- Customer Detail Modal -->
            <livewire:admin.customers.customer-detail-modal />
        </div>
    </div>
</div>