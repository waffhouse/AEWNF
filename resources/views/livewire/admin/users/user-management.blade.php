<div>
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold flex items-center text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            User Management
        </h2>
        
        @can('create users')
        <button wire:click="openUserFormModal" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New User
        </button>
        @endif
    </div>
    
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
                            Name
                        </th>
                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Email
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
                                @if($user->hasRole('customer') && $user->customer_number)
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
                                    wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
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
                
                // Ensure it's reconnected after filter changes
                $wire.on('resetUsers', () => {
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

    <!-- User Form Modal -->
    <div
        x-data="{ open: false }"
        x-show="open"
        x-on:open-user-form-modal.window="open = true"
        x-on:close-user-form-modal.window="open = false"
        x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        style="display: none;"
    >
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">{{ $editUserId ? 'Edit User' : 'Create New User' }}</h3>
                <button type="button" x-on:click="open = false" class="text-gray-400 hover:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            
            <form wire:submit.prevent="{{ $editUserId ? 'updateUser' : 'createUser' }}">
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input wire:model="name" id="name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input wire:model="email" id="email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password {{ $editUserId ? '(Leave blank to keep current)' : '' }}
                        </label>
                        <div class="relative">
                            <input 
                                wire:model="password" 
                                id="password" 
                                :type="showPassword ? 'text' : 'password'" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pr-10"
                            >
                            <button 
                                type="button"
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                            >
                                <svg 
                                    x-show="!showPassword" 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    class="h-5 w-5" 
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg 
                                    x-show="showPassword" 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    class="h-5 w-5" 
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="userRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select wire:model.live="userRole" id="userRole" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('userRole') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    @can('create users')
                        <div x-data="{ show: false }" x-init="$watch('$wire.userRole', value => show = value === 'customer')" x-show="show">
                            <label for="customer_number" class="block text-sm font-medium text-gray-700 mb-1">Customer Number (4 digits)</label>
                            <input wire:model="customer_number" id="customer_number" type="text" maxlength="4" placeholder="e.g. 1234" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="text-xs text-gray-500 mt-1">Unique identifier for customer (must be 4 digits and unique)</div>
                            @error('customer_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endcan
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="open = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        {{ $editUserId ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Messages removed from here - now using the global notification component in dashboard.blade.php -->
</div>