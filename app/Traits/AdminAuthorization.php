<?php

namespace App\Traits;

trait AdminAuthorization
{
    /**
     * Check if user can access admin dashboard
     */
    protected function authorizeAdminAccess()
    {
        // Allow access if user has any of these permissions
        if (!auth()->user()->hasAnyPermission(['access admin dashboard', 'view users', 'manage orders', 'view all orders'])) {
            abort(403, 'Unauthorized action. You do not have permission to access the admin dashboard.');
        }
    }
    
    /**
     * Central method to check if the current user can perform a specific action
     * This prevents permission checks from being bypassed or inconsistently applied
     * 
     * Always checks for the specific permission without role-based bypasses
     */
    protected function authorizeAction($permission)
    {
        $user = auth()->user();
        
        // Check for the specific permission
        if (!$user->hasPermissionTo($permission)) {
            \Log::error('Permission denied: User ' . $user->name . 
                       ' with roles ' . implode(',', $user->getRoleNames()->toArray()) . 
                       ' attempted to perform action requiring permission: ' . $permission);
            
            // Using throw instead of abort() for Livewire components
            throw new \Illuminate\Auth\Access\AuthorizationException('You do not have permission to perform this action.');
        }
        
        return true;
    }
}