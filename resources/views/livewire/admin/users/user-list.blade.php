<div>
    <!-- Search Bar -->
    <div class="mb-4">
        <div class="flex items-center border rounded-md overflow-hidden shadow-sm">
            <div class="px-3 py-2 bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                wire:model.live.debounce.300ms="userSearch" 
                type="text" 
                placeholder="Search users by name or email..." 
                class="flex-1 px-3 py-2 text-sm focus:outline-none border-none"
            >
        </div>
    </div>
    
    <!-- Responsive User List -->
    <div class="rounded-lg border border-gray-200">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('name')" class="flex items-center">
                                Name
                                @if($sortField === 'name')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            <button wire:click="sortBy('email')" class="flex items-center">
                                Email
                                @if($sortField === 'email')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                            Role
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Customer #
                        </th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900 break-words max-w-[150px]">
                                {{ $user->name }}
                                <!-- Mobile view extra info -->
                                <div class="sm:hidden mt-1">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($role->name === 'admin') bg-red-100 text-red-800
                                            @elseif($role->name === 'staff') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="md:hidden text-xs text-gray-500 mt-1 truncate">
                                    {{ $user->email }}
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-500 hidden md:table-cell break-words max-w-[200px]">
                                {{ $user->email }}
                            </td>
                            <td class="px-3 py-2 hidden sm:table-cell">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($role->name === 'admin') bg-red-100 text-red-800
                                        @elseif($role->name === 'staff') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-500 hidden lg:table-cell">
                                @if($user->customer_number)
                                    <span class="font-mono">{{ $user->customer_number }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right text-sm font-medium">
                                @can('edit users')
                                <button wire:click="openUserEdit({{ $user->id }})" class="text-blue-600 hover:text-blue-900">
                                    <span class="sr-only sm:not-sr-only">Edit</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:hidden inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                @endcan

                                @can('delete users')
                                <button 
                                    wire:click="confirmDeleteUser({{ $user->id }})" 
                                    class="text-red-600 hover:text-red-900 ml-3"
                                >
                                    <span class="sr-only sm:not-sr-only">Delete</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:hidden inline-block" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    
                    @if(count($users) === 0)
                        <tr>
                            <td colspan="5" class="px-3 py-3 text-center text-sm text-gray-500">
                                No users found matching your search criteria.
                            </td>
                        </tr>
                    @endif
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
                
                // Listen for reset event
                window.addEventListener('resetInfiniteScroll', () => {
                    this.setupObserver();
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
            <svg class="animate-spin h-6 w-6 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-sm text-gray-600">Loading more users...</span>
        </div>

        <!-- End of Results Message -->
        <div x-show="!@js($hasMorePages)" class="py-4 text-sm text-gray-600">
            @if($totalCount === 0)
                No users found
            @elseif($loadedCount === 1)
                Showing 1 user
            @else
                Showing all {{ $loadedCount }} users
            @endif
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <x-modals.confirmation-modal
        name="delete-user-confirmation"
        title="Delete User"
        confirm-text="Delete"
        cancel-text="Cancel"
        confirm-method="deleteUser"
        confirm-color="red"
        icon="error"
    >
        <p class="text-sm text-gray-600">
            Are you sure you want to delete this user? This action cannot be undone and will permanently remove the user and all associated data.
        </p>
    </x-modals.confirmation-modal>
</div>