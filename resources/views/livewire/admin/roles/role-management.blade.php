<div>
    <!-- Scroll to top button -->
    <x-scroll-to-top />
    
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold flex items-center text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Role Management
        </h2>
        
        @can('manage roles')
        <button 
            wire:click="$dispatch('open-new-role-form')" 
            class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Role
        </button>
        @endcan
    </div>
    
    <!-- Role List Component -->
    <livewire:admin.roles.role-list />
    
    <!-- Role Form Component -->
    <livewire:admin.roles.role-form />
</div>