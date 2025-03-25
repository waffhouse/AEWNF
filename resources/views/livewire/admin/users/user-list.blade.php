<div>
    <!-- Search and Filter Controls -->
    <div class="mb-4 space-y-2">
        <div class="flex flex-wrap gap-2 items-center">
            <!-- Search Input -->
            <form wire:submit.prevent="searchUsers" class="flex flex-1 items-center">
                <div class="flex flex-1 items-center border rounded-md overflow-hidden shadow-sm">
                    <div class="px-3 py-2 bg-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        wire:model="userSearch" 
                        type="text" 
                        id="user-search" 
                        placeholder="Search users by name or email..." 
                        class="flex-1 px-3 py-2 text-sm focus:outline-none border-none"
                    >
                </div>
                <button type="submit" class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition-colors duration-150">
                    Search
                </button>
            </form>
            
            <!-- Role Filter Dropdown -->
            <div class="relative flex items-center">
                <label for="role-filter" class="sr-only">Filter by role</label>
                <div class="relative">
                    <select 
                        id="role-filter"
                        wire:model.live="roleFilter" 
                        class="pl-9 pr-8 py-2 text-sm bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Filters Badges -->
        <x-filters.filter-badges 
            :filters="$activeFilters" 
            resetAllEvent="resetAllFilters" 
            class="mt-2"
        />
        
        <!-- Admin Tools Section -->
        @can('manage roles')
        <div class="flex justify-end mt-2">
            <button 
                type="button" 
                wire:click="verifyDatabaseConsistency"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-gray-500"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Verify Database
            </button>
        </div>
        @endcan
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
    
    <!-- Standard Pagination -->
    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <!-- Loading Indicator -->
    <div wire:loading wire:target="searchUsers, resetSearch, resetRoleFilter, resetAllFilters, roleFilter, sortBy" class="mt-4 text-center py-2">
        <svg class="animate-spin h-5 w-5 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="ml-2 text-sm text-gray-600">Loading...</span>
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
    
    <!-- Database Verification Modal -->
    <x-modals.detail-modal
        name="database-verification-modal"
        title="Database Verification"
        maxWidth="5xl"
        hidePrimaryAction="true"
    >
        <div 
            x-data="{
                users: [],
                init() {
                    // Listen for database verification data
                    window.addEventListener('database-verification-data', (event) => {
                        if (event.detail) {
                            this.users = event.detail;
                        } else {
                            this.users = [];
                        }
                    });
                }
            }"
            class="space-y-4"
        >
            <div class="mb-2 p-3 bg-blue-50 rounded-md border border-blue-200">
                <p class="text-sm text-blue-800">
                    <span class="font-medium">Verification Tool:</span> This shows raw database query results to verify that what's displayed in the user interface matches the actual database records.
                </p>
            </div>
            
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer #</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-if="users.length === 0">
                            <tr>
                                <td colspan="5" class="px-3 py-3 text-center text-sm text-gray-500">
                                    Loading database records...
                                </td>
                            </tr>
                        </template>
                        <template x-for="user in users" :key="user.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm text-gray-500" x-text="user.id"></td>
                                <td class="px-3 py-2 text-sm font-medium text-gray-900" x-text="user.name"></td>
                                <td class="px-3 py-2 text-sm text-gray-500" x-text="user.email"></td>
                                <td class="px-3 py-2 text-sm text-gray-500">
                                    <span 
                                        x-text="user.role" 
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-red-100 text-red-800': user.role === 'admin',
                                            'bg-blue-100 text-blue-800': user.role === 'staff',
                                            'bg-green-100 text-green-800': user.role && user.role.includes('customer')
                                        }"
                                    ></span>
                                </td>
                                <td class="px-3 py-2 text-sm font-mono text-gray-500" x-text="user.customer_number || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- No custom footer needed - the modal has a built-in close button -->
        </div>
    </x-modals.detail-modal>
</div>