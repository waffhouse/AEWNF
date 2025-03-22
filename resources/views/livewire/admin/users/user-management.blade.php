<div>
    <!-- Scope the whole component to isolate Alpine context -->
    <div x-data="{}" id="user-management-component">
        <!-- Scroll to top button -->
        <x-scroll-to-top />
        
        <div class="space-y-6">
            <!-- Users Header -->
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold flex items-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    User Management
                </h2>
                
                @can('create users')
                <button 
                    wire:click="$dispatch('open-new-user-form')" 
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New User
                </button>
                @endif
            </div>
            
            <!-- User List Component -->
            <livewire:admin.users.user-list />
        </div>
        
        <!-- User Form Component (shown as modal) -->
        <livewire:admin.users.user-form />
    </div>
</div>