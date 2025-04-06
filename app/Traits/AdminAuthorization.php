<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

/**
 * Trait AdminAuthorization
 *
 * Provides standardized authorization functionality for admin components.
 * Centralizes permission checks and access control for admin actions.
 */
trait AdminAuthorization
{
    /**
     * Permission context for the current operation
     * Useful for logging and error messages
     */
    protected string $currentContext = 'admin';

    /**
     * Check if user can access admin dashboard
     *
     * @throws AuthorizationException
     */
    protected function authorizeAdminAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            Log::warning('Unauthorized admin access attempt: User not authenticated');
            throw new AuthorizationException('You must be logged in to access the admin area.');
        }

        $adminPermissions = [
            'access admin dashboard',
            'view users',
            'manage orders',
            'view all orders',
            'manage roles',
            'manage permissions',
        ];

        // Allow access if user has any of these permissions
        if (! $user->hasAnyPermission($adminPermissions)) {
            Log::warning('Unauthorized admin access attempt: User '.$user->name.
                ' with roles '.implode(',', $user->getRoleNames()->toArray()));

            throw new AuthorizationException('Unauthorized action. You do not have permission to access the admin dashboard.');
        }

        return true;
    }

    /**
     * Central method to check if the current user can perform a specific action
     * This prevents permission checks from being bypassed or inconsistently applied
     *
     * @param  string|array  $permission  Permission or array of permissions (any one is sufficient)
     * @param  string|null  $message  Custom error message
     * @param  string|null  $context  Operation context for logging
     *
     * @throws AuthorizationException
     */
    protected function authorizeAction($permission, ?string $message = null, ?string $context = null): bool
    {
        $user = auth()->user();

        if (! $user) {
            Log::warning('Authorization check failed: No authenticated user');
            throw new AuthorizationException('You must be logged in to perform this action.');
        }

        $context = $context ?? $this->currentContext;
        $permissionDesc = is_array($permission) ? implode(', ', $permission) : $permission;

        // Check for the permission(s)
        $hasPermission = is_array($permission)
            ? $user->hasAnyPermission($permission)
            : $user->hasPermissionTo($permission);

        if (! $hasPermission) {
            Log::warning("Permission denied [$context]: User {$user->name} ".
                'with roles '.implode(',', $user->getRoleNames()->toArray()).
                " attempted action requiring permission: {$permissionDesc}");

            $errorMessage = $message ?? 'You do not have permission to perform this action.';
            throw new AuthorizationException($errorMessage);
        }

        return true;
    }

    /**
     * Check if user has all of the given permissions
     *
     * @param  array  $permissions  Array of permissions (all are required)
     * @param  string|null  $message  Custom error message
     * @param  string|null  $context  Operation context for logging
     *
     * @throws AuthorizationException
     */
    protected function authorizeAllActions(array $permissions, ?string $message = null, ?string $context = null): bool
    {
        $user = auth()->user();

        if (! $user) {
            Log::warning('Authorization check failed: No authenticated user');
            throw new AuthorizationException('You must be logged in to perform this action.');
        }

        $context = $context ?? $this->currentContext;

        // Check for all permissions
        if (! $user->hasAllPermissions($permissions)) {
            Log::warning("Permission denied [$context]: User {$user->name} ".
                'with roles '.implode(',', $user->getRoleNames()->toArray()).
                ' attempted action requiring all permissions: '.implode(', ', $permissions));

            $errorMessage = $message ?? 'You do not have all required permissions to perform this action.';
            throw new AuthorizationException($errorMessage);
        }

        return true;
    }

    /**
     * Set the current operation context for improved logging and error messages
     *
     * @param  string  $context  The operation context (e.g., 'users', 'orders', 'inventory')
     */
    protected function withContext(string $context): self
    {
        $this->currentContext = $context;

        return $this;
    }

    /**
     * Check if the current user has a specific permission
     * Unlike authorizeAction, this doesn't throw an exception, just returns boolean
     *
     * @param  string|array  $permission  Permission or array of permissions
     */
    protected function userCan($permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return is_array($permission)
            ? $user->hasAnyPermission($permission)
            : $user->hasPermissionTo($permission);
    }

    /**
     * Check if the current user has all of the given permissions
     * Unlike authorizeAllActions, this doesn't throw an exception, just returns boolean
     *
     * @param  array  $permissions  Array of permissions
     */
    protected function userCanAll(array $permissions): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAllPermissions($permissions);
    }

    /**
     * Authorize a CRUD operation based on resource and action
     *
     * @param  string  $resource  The resource name (e.g., 'users', 'orders', 'products')
     * @param  string  $action  The action (create, view, edit, delete)
     * @param  string|null  $message  Custom error message
     *
     * @throws AuthorizationException
     */
    protected function authorizeCrud(string $resource, string $action, ?string $message = null): bool
    {
        $permissionMap = [
            'create' => "create {$resource}",
            'view' => "view {$resource}",
            'edit' => "edit {$resource}",
            'delete' => "delete {$resource}",
            'manage' => "manage {$resource}",
        ];

        if (! isset($permissionMap[$action])) {
            throw new \InvalidArgumentException("Invalid CRUD action: {$action}");
        }

        return $this->withContext($resource)->authorizeAction(
            $permissionMap[$action],
            $message ?? "You don't have permission to {$action} {$resource}."
        );
    }
}
