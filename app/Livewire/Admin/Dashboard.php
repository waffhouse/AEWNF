<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Traits\AdminAuthorization;
use App\Models\User;
use App\Models\Order;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Dashboard extends Component
{
    use AdminAuthorization;
    
    // Active tab tracking
    public $activeTab = '';
    
    // Listeners for events
    protected $listeners = [
        'tabChanged' => 'setActiveTab'
    ];
    
    // Tab definitions with their metadata
    protected $tabs = [
        'users' => [
            'name' => 'Users',
            'permissions' => ['access admin dashboard'],
            'icon' => 'users',
            'color' => 'blue',
            'countModel' => \App\Models\User::class
        ],
        'roles' => [
            'name' => 'Roles',
            'permissions' => ['manage roles'],
            'icon' => 'role',
            'color' => 'green',
            'countModel' => \Spatie\Permission\Models\Role::class
        ],
        'permissions' => [
            'name' => 'Permissions',
            'permissions' => ['manage permissions'],
            'icon' => 'key',
            'color' => 'purple',
            'countModel' => \Spatie\Permission\Models\Permission::class
        ],
        'inventory-sync' => [
            'name' => 'Inventory',
            'permissions' => ['sync inventory'],
            'icon' => 'tag',
            'color' => 'amber',
            'countModel' => null
        ],
        'orders' => [
            'name' => 'Orders',
            'permissions' => ['manage orders', 'view all orders'],
            'icon' => 'shopping-bag',
            'color' => 'red',
            'countModel' => \App\Models\Order::class
        ]
    ];
    
    public function mount()
    {
        // Basic check - only users with appropriate permissions can access this dashboard
        $this->authorizeAdminAccess();
        
        // Set default active tab based on permissions
        $this->activeTab = $this->determineDefaultTab();
    }
    
    /**
     * Determine the default tab based on user permissions
     */
    protected function determineDefaultTab(): string
    {
        $user = auth()->user();
        
        // Priority order for tabs
        $priorities = ['orders', 'users', 'inventory-sync', 'roles', 'permissions'];
        
        foreach ($priorities as $tabId) {
            if ($this->userCanAccessTab($tabId)) {
                return $tabId;
            }
        }
        
        // Fallback to first available tab
        foreach ($this->tabs as $tabId => $tab) {
            if ($this->userCanAccessTab($tabId)) {
                return $tabId;
            }
        }
        
        return '';
    }
    
    /**
     * Check if user can access a specific tab
     */
    protected function userCanAccessTab(string $tabId): bool
    {
        if (!isset($this->tabs[$tabId])) {
            return false;
        }
        
        $user = auth()->user();
        $permissions = $this->tabs[$tabId]['permissions'] ?? [];
        
        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }
        
        return empty($permissions);
    }
    
    /**
     * Set the active tab
     */
    public function setActiveTab($tab)
    {
        if ($this->userCanAccessTab($tab)) {
            $this->activeTab = $tab;
            $this->dispatch('activeTabChanged', $tab);
        }
    }
    
    /**
     * Get the list of tabs user can access
     */
    protected function getAccessibleTabs(): array
    {
        return array_filter($this->tabs, function($tab, $tabId) {
            return $this->userCanAccessTab($tabId);
        }, ARRAY_FILTER_USE_BOTH);
    }
    
    /**
     * Get the count for a model associated with a tab
     */
    protected function getTabCount(string $tabId): ?int
    {
        $modelClass = $this->tabs[$tabId]['countModel'] ?? null;
        if ($modelClass && class_exists($modelClass)) {
            return $modelClass::count();
        }
        return null;
    }
    
    public function render()
    {
        $accessibleTabs = $this->getAccessibleTabs();
        $tabCounts = [];
        
        // Get counts for tabs
        foreach ($accessibleTabs as $tabId => $tab) {
            $tabCounts[$tabId] = $this->getTabCount($tabId);
        }
        
        return view('livewire.admin.dashboard', [
            'accessibleTabs' => $accessibleTabs,
            'tabCounts' => $tabCounts,
            'totalUsers' => User::count(),
            'totalRoles' => Role::count(),
            'totalPermissions' => Permission::count(),
            'totalOrders' => Order::count(),
            'canManageUsers' => auth()->user()->hasPermissionTo('access admin dashboard'),
            'canManageRoles' => auth()->user()->hasPermissionTo('manage roles'),
            'canManagePermissions' => auth()->user()->hasPermissionTo('manage permissions'),
            'canSyncInventory' => auth()->user()->hasPermissionTo('sync inventory'),
            'canManageOrders' => auth()->user()->hasPermissionTo('manage orders') || auth()->user()->hasPermissionTo('view all orders'),
        ])->layout('layouts.app');
    }
}