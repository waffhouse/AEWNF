<div>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="pb-8">
            <h1 class="text-2xl font-semibold text-gray-900">Sales Synchronization</h1>
            <p class="mt-2 text-sm text-gray-700">Synchronize sales transactions from NetSuite</p>
        </div>
        
        <!-- Sync Tool -->
        <div>
            <livewire:admin.sales.sales-sync />
        </div>
        
        <!-- Information Box -->
        <div class="mt-8 bg-blue-50 p-6 rounded-lg border border-blue-200">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">About Sales Synchronization</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Use this tool to synchronize sales data from NetSuite. The synchronization process will:</p>
                        <ul class="list-disc pl-5 mt-2 space-y-1">
                            <li>Import new sales transactions from NetSuite</li>
                            <li>Update existing transactions with the latest information</li>
                            <li>Process both invoices and credit memos properly</li>
                        </ul>
                        <p class="mt-2">Analytics and detailed sales listings have been moved to dedicated sections.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>