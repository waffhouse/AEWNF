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
                    // Use history.replaceState to avoid adding a history entry
                    history.replaceState(null, null, `#${value}`);
                }
            });
            
            // Handle hash changes from browser back/forward buttons
            window.addEventListener('hashchange', () => {
                const hashValue = window.location.hash.substring(1);
                // Only process if the hash value is actually valid
                if (hashValue && Object.keys($wire.accessibleTabs).includes(hashValue)) {
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
                                    Manage users, roles, permissions, inventory synchronization, sales data, and orders.
                                @elseif(auth()->user()->hasPermissionTo('manage orders') || auth()->user()->hasPermissionTo('view all orders'))
                                    Manage customer orders and track order history.
                                @elseif(auth()->user()->hasPermissionTo('view netsuite sales data'))
                                    View and manage sales transaction data.
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
                    x-data="{ 
                        show: false, 
                        message: '', 
                        type: '',
                        timeout: null,
                        displayMessage(msg, msgType) {
                            this.show = true;
                            this.message = msg;
                            this.type = msgType;
                            clearTimeout(this.timeout);
                            this.timeout = setTimeout(() => this.show = false, 3000);
                        }
                    }"
                    x-show="show"
                    x-init="
                        @if(session()->has('message'))
                            displayMessage('{{ session('message') }}', 'success');
                        @endif
                        @if(session()->has('error'))
                            displayMessage('{{ session('error') }}', 'error');
                        @endif
                        $wire.on('message', (msg) => displayMessage(msg, 'success'));
                        $wire.on('error', (msg) => displayMessage(msg, 'error'));
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
                    <div wire:key="users-tab-panel-container">
                        @if($canManageUsers)
                        <x-admin.tab-content id="users">
                            <div id="users-tab-wrapper">
                                @livewire('admin.users.user-management', [], key('users-management'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>
                    
                    <!-- Roles Tab Panel -->
                    <div wire:key="roles-tab-panel-container">
                        @if($canManageRoles)
                        <x-admin.tab-content id="roles">
                            <div id="roles-tab-wrapper">
                                @livewire('admin.roles.role-management', [], key('roles-management'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>
                    
                    <!-- Permissions Tab Panel -->
                    <div wire:key="permissions-tab-panel-container">
                        @if($canManagePermissions)
                        <x-admin.tab-content id="permissions">
                            <div id="permissions-tab-wrapper">
                                @livewire('admin.permissions.permission-management', [], key('permissions-management'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>

                    <!-- Inventory Sync Tab Panel -->
                    <div wire:key="inventory-sync-tab-panel-container">
                        @if($canSyncInventory)
                        <x-admin.tab-content id="inventory-sync">
                            <div id="inventory-sync-tab-wrapper">
                                @livewire('admin.inventory.inventory-sync', [], key('inventory-sync'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>

                    <!-- Orders Tab Panel -->
                    <div wire:key="orders-tab-panel-container">
                        @if($canManageOrders)
                        <x-admin.tab-content id="orders">
                            <div id="orders-tab-wrapper">
                                @livewire('admin.orders.order-management', [], key('orders-management'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>
                    
                    <!-- Sales Tab Panel -->
                    <div wire:key="sales-tab-panel-container">
                        @if($canManageSales)
                        <x-admin.tab-content id="sales">
                            <div id="sales-tab-wrapper">
                                @livewire('admin.sales.sales-dashboard', [], key('sales-dashboard'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>
                    
                    <!-- Featured Brands Tab Panel -->
                    <div wire:key="featured-brands-tab-panel-container">
                        @if($canManageFeaturedBrands)
                        <x-admin.tab-content id="featured-brands">
                            <div id="featured-brands-tab-wrapper">
                                @livewire('admin.featured-brands.featured-brand-management', [], key('featured-brands-management'))
                            </div>
                        </x-admin.tab-content>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>