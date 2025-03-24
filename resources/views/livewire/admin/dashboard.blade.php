<div 
    x-data="{ 
        activeTab: '{{ $activeTab }}',
        init() {
            // Set initial tab from URL hash if present
            if (window.location.hash) {
                const hashValue = window.location.hash.substring(1);
                this.activeTab = hashValue;
                $wire.setActiveTab(hashValue);
            } else {
                // Set hash from initial tab
                window.location.hash = this.activeTab;
            }
            
            // Update hash when tab changes (but don't trigger another change)
            this.$watch('activeTab', (value) => {
                if (window.location.hash !== `#${value}`) {
                    window.location.hash = value;
                }
            });
            
            // Handle hash changes from browser back/forward buttons
            window.addEventListener('hashchange', () => {
                const hashValue = window.location.hash.substring(1);
                if (hashValue && this.activeTab !== hashValue) {
                    this.activeTab = hashValue;
                    $wire.setActiveTab(hashValue);
                }
            });
        }
    }" 
    class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Compact Header with Stats -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
            <div class="p-6 text-gray-900">
                <div class="mb-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h2 class="text-xl font-semibold">
                                @if(auth()->user()->hasPermissionTo('access admin dashboard'))
                                    Management Dashboard
                                @else
                                    Order Management
                                @endif
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                @if(auth()->user()->hasPermissionTo('access admin dashboard'))
                                    Manage users, roles, permissions, inventory synchronization, and orders.
                                @elseif(auth()->user()->hasPermissionTo('manage orders') || auth()->user()->hasPermissionTo('view all orders'))
                                    Manage customer orders and track order history.
                                @else
                                    Access admin features based on your permissions.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Navigation -->
                <x-admin.tab-navigation :tabs="$accessibleTabs" :counts="$tabCounts" />
            </div>
        </div>
        
        <!-- Tab Content Container -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Flash Messages - Placed at the top level for all tabs -->
                <div 
                    x-data="{ show: false, message: '', type: '' }"
                    x-show="show"
                    x-init="
                        @if(session()->has('message'))
                            show = true;
                            message = '{{ session('message') }}';
                            type = 'success';
                            setTimeout(() => show = false, 3000);
                        @endif
                        @if(session()->has('error'))
                            show = true;
                            message = '{{ session('error') }}';
                            type = 'error';
                            setTimeout(() => show = false, 3000);
                        @endif
                        $wire.on('message', (msg) => {
                            show = true;
                            message = msg;
                            type = 'success';
                            setTimeout(() => show = false, 3000);
                        });
                        $wire.on('error', (msg) => {
                            show = true;
                            message = msg;
                            type = 'error';
                            setTimeout(() => show = false, 3000);
                        });
                    "
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="fixed top-4 right-4 z-50 w-72 p-4 rounded shadow-lg"
                    :class="type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                    <div class="flex items-center justify-between">
                        <span x-text="message"></span>
                        <button 
                            @click="show = false" 
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Tab Contents using Modular Components -->
                <div>
                    <!-- Users Tab Panel -->
                    @if($canManageUsers)
                    <x-admin.tab-content id="users">
                        <livewire:admin.users.user-management />
                    </x-admin.tab-content>
                    @endif
                    
                    <!-- Roles Tab Panel -->
                    @if($canManageRoles)
                    <x-admin.tab-content id="roles">
                        <livewire:admin.roles.role-management />
                    </x-admin.tab-content>
                    @endif
                    
                    <!-- Permissions Tab Panel -->
                    @if($canManagePermissions)
                    <x-admin.tab-content id="permissions">
                        <livewire:admin.permissions.permission-management />
                    </x-admin.tab-content>
                    @endif

                    <!-- Inventory Sync Tab Panel -->
                    @if($canSyncInventory)
                    <x-admin.tab-content id="inventory-sync">
                        <livewire:admin.inventory.inventory-sync />
                    </x-admin.tab-content>
                    @endif

                    <!-- Orders Tab Panel -->
                    @if($canManageOrders)
                    <x-admin.tab-content id="orders">
                        {{-- Use @livewire directive instead of livewire: tag to prevent double parsing --}}
                        @livewire('admin.orders.order-management')
                    </x-admin.tab-content>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>