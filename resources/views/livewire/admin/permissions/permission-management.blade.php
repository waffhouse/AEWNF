<div>
    <!-- Scroll to top button -->
    <x-scroll-to-top />
    
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold flex items-center text-purple-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            Permission Management
        </h2>
        
        @can('manage permissions')
        <button 
            wire:click="$dispatch('open-new-permission-form')" 
            class="inline-flex items-center px-3 py-1.5 bg-purple-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Permission
        </button>
        @endcan
    </div>
    
    <!-- Permission List Component -->
    <livewire:admin.permissions.permission-list />
    
    <!-- Permission Form Component -->
    <livewire:admin.permissions.permission-form />
</div>