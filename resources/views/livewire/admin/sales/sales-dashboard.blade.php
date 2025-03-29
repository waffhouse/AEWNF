<div>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="pb-8">
            <h1 class="text-2xl font-semibold text-gray-900">Sales Dashboard</h1>
            <p class="mt-2 text-sm text-gray-700">View and manage sales transactions from NetSuite</p>
        </div>
        
        <!-- Sync Tool -->
        <div class="mb-8">
            <livewire:admin.sales.sales-sync />
        </div>
        
        <!-- Analytics Component -->
        <div class="mb-8">
            <livewire:admin.sales.sales-analytics />
        </div>
        
        <!-- Sales List -->
        <div>
            <livewire:admin.sales.sales-list />
        </div>
    </div>
</div>