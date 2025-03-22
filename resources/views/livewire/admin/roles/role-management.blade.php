<div>
    <!-- Scroll to top button -->
    <x-scroll-to-top />
    
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold flex items-center text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Role Management
        </h2>
        
        @can('manage roles')
        <button wire:click="openRoleFormModal" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Role
        </button>
        @endcan
    </div>
    
    <!-- Responsive Role List -->
    <div class="rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Permissions
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                            Users
                        </th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">
                                {{ $role->name }}
                                
                                <!-- Mobile view extras -->
                                <div class="md:hidden mt-1">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($role->permissions->take(2) as $permission)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                        @if($role->permissions->count() > 2)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                +{{ $role->permissions->count() - 2 }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="sm:hidden text-xs text-gray-500 mt-1">
                                    {{ $role->users_count }} users
                                </div>
                            </td>
                            <td class="px-3 py-2 hidden md:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($role->permissions->take(3) as $permission)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                    @if($role->permissions->count() > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            +{{ $role->permissions->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-500 hidden sm:table-cell">
                                {{ $role->users_count }}
                            </td>
                            <td class="px-3 py-2 text-right text-sm font-medium">
                                @can('manage roles')
                                <button wire:click="editRoleModal({{ $role->id }})" class="text-blue-600 hover:text-blue-900">
                                    <span class="sr-only sm:not-sr-only">Edit</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:hidden inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDeleteRole({{ $role->id }})" class="text-red-600 hover:text-red-900 ml-3">
                                    <span class="sr-only sm:not-sr-only">Delete</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:hidden inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Infinite Scroll Controls -->
    <div 
        class="mt-8 text-center" 
        x-data="{ 
            observer: null,
            init() {
                // Initialize the observer
                this.setupObserver();
                
                // Ensure it's reconnected after filter changes
                $wire.on('resetRoles', () => {
                    // Small delay to ensure DOM is updated
                    setTimeout(() => this.setupObserver(), 50);
                });
            },
            setupObserver() {
                // Clean up any existing observer
                if (this.observer) {
                    this.observer.disconnect();
                }
                
                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            @this.loadMore();
                        }
                    });
                }, { rootMargin: '100px' });
                
                this.observer.observe(this.$el);
            },
            // Ensure proper cleanup
            destroy() {
                if (this.observer) {
                    this.observer.disconnect();
                }
            }
        }"
    >
        <!-- Loading Indicator -->
        <div wire:loading wire:target="loadMore" class="py-4">
            <svg class="animate-spin h-6 w-6 text-green-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-sm text-gray-600">Loading more roles...</span>
        </div>

        <!-- End of Results Message -->
        <div x-show="!@js($hasMorePages)" class="py-4 text-sm text-gray-600">
            @if($totalCount === 0)
                No roles found
            @elseif($loadedCount === 1)
                Showing 1 role
            @else
                Showing all {{ $loadedCount }} roles
            @endif
        </div>
    </div>
    
    <!-- Role Form Modal -->
    <div
        x-data="{ open: false }"
        x-show="open"
        x-on:open-role-form-modal.window="open = true"
        x-on:close-role-form-modal.window="open = false"
        x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        style="display: none;"
    >
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">{{ $editRoleId ? 'Edit Role' : 'Create New Role' }}</h3>
                <button type="button" x-on:click="open = false" class="text-gray-400 hover:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <form wire:submit.prevent="{{ $editRoleId ? 'updateRole' : 'createRole' }}">
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="roleName" class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                        <input wire:model="roleName" id="roleName" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('roleName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
                        <div class="bg-white p-3 border border-gray-300 rounded-md max-h-48 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($allPermissions as $permission)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" wire:model="rolePermissions" value="{{ $permission->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('rolePermissions') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="open = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        {{ $editRoleId ? 'Update Role' : 'Create Role' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Messages removed from here - now using the global notification component in dashboard.blade.php -->
</div>