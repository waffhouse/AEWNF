<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Traits\AdminAuthorization;

abstract class AdminComponent extends Component
{
    use AdminAuthorization;
    
    // Common properties for all admin components
    protected int $perPage = 10;
    protected string $searchQuery = '';
    
    public function mount()
    {
        // Basic check - only users with proper permissions can access admin components
        $this->authorizeAdminAccess();
        
        // Check if user has at least one of the required permissions for this component
        $this->checkRequiredPermissions();
        
        $this->mountComponent();
    }
    
    /**
     * Check if the user has at least one of the required permissions for this component
     */
    protected function checkRequiredPermissions(): void
    {
        $requiredPermissions = $this->getRequiredPermissions();
        $user = auth()->user();
        
        if (empty($requiredPermissions)) {
            // No specific permissions required, proceed
            return;
        }
        
        // Check if user has any of the required permissions
        foreach ($requiredPermissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return;
            }
        }
        
        // If we get here, user has none of the required permissions
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'You do not have any of the required permissions to access this component.'
        );
    }
    
    /**
     * Flash a success message
     */
    protected function flashSuccess(string $message): void
    {
        $this->dispatch('message', $message);
    }
    
    /**
     * Flash an error message
     */
    protected function flashError(string $message): void
    {
        $this->dispatch('error', $message);
    }
    
    /**
     * Handle search query changes
     */
    public function updatedSearchQuery(): void
    {
        // Child components should implement their own search reset logic
    }
    
    /**
     * Each child component must define its required permissions
     * 
     * @return array
     */
    abstract public function getRequiredPermissions(): array;
    
    /**
     * Child components can override this for additional setup
     */
    protected function mountComponent(): void
    {
        // Implement in child components if needed
    }
}