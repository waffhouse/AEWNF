<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Collection;

class TabManager
{
    private Collection $tabs;
    private string $activeTab;

    public function __construct()
    {
        $this->tabs = collect([]);
        $this->activeTab = '';
    }

    /**
     * Add a tab definition with its permissions
     */
    public function addTab(string $id, string $name, string $icon, string $color, array $permissions = [], ?string $countMethod = null): self
    {
        $this->tabs->put($id, [
            'id' => $id,
            'name' => $name,
            'icon' => $icon,
            'color' => $color,
            'permissions' => $permissions,
            'countMethod' => $countMethod
        ]);
        
        return $this;
    }

    /**
     * Get only tabs the current user has permission to see
     */
    public function getAuthorizedTabs(): Collection
    {
        $user = auth()->user();
        
        return $this->tabs->filter(function ($tab) use ($user) {
            // If no permissions required, tab is always visible
            if (empty($tab['permissions'])) {
                return true;
            }
            
            // Otherwise check each permission
            foreach ($tab['permissions'] as $permission) {
                if ($user->hasPermissionTo($permission)) {
                    return true;
                }
            }
            
            return false;
        });
    }

    /**
     * Set the active tab
     */
    public function setActiveTab(string $tabId): void
    {
        // Only set if tab exists and user has access
        if ($this->getAuthorizedTabs()->has($tabId)) {
            $this->activeTab = $tabId;
        } else {
            // Default to first available tab
            $this->activeTab = $this->getAuthorizedTabs()->keys()->first() ?? '';
        }
    }

    /**
     * Get active tab
     */
    public function getActiveTab(): string
    {
        return $this->activeTab;
    }

    /**
     * Determine best default tab based on user permissions
     */
    public function determineDefaultTab(): string
    {
        $user = auth()->user();
        $tabs = $this->getAuthorizedTabs();
        
        // Priority order for tabs
        $priorities = ['orders', 'users', 'inventory-sync', 'roles', 'permissions'];
        
        foreach ($priorities as $tabId) {
            if ($tabs->has($tabId)) {
                return $tabId;
            }
        }
        
        // Fallback to first available tab
        return $tabs->keys()->first() ?? '';
    }
}