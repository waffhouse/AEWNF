<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // In Laravel 11, middleware in controllers is applied differently
    // We'll check permissions directly in each method

    /**
     * View dashboard page
     */
    public function viewDashboard()
    {
        $user = auth()->user();
        Log::info('User '.$user->name.' accessing admin dashboard');

        // Check if user has permission to access admin dashboard
        if (! $user->hasPermissionTo('access admin dashboard')) {
            Log::error('Access denied: User '.$user->name.' attempted to access dashboard without admin dashboard permission');
            abort(403, 'You do not have permission to access the admin dashboard');
        }

        // Return the Livewire component directly, not a view
        return view('livewire.admin.dashboard');
    }

    // viewUsers method removed - functionality now provided by the admin dashboard

    /**
     * View roles management page
     */
    public function viewRoles()
    {
        Log::info('User '.auth()->user()->name.' accessing role management');

        if (! auth()->user()->hasPermissionTo('manage roles')) {
            abort(403, 'You do not have permission to manage roles');
        }

        return view('admin.roles-management');
    }

    /**
     * View permissions management page
     */
    public function viewPermissions()
    {
        Log::info('User '.auth()->user()->name.' accessing permission management');

        if (! auth()->user()->hasPermissionTo('manage permissions')) {
            abort(403, 'You do not have permission to manage permissions');
        }

        return view('admin.permissions-management');
    }

    /**
     * Redirect to appropriate dashboard based on user permissions
     */
    public function redirectToDashboard()
    {
        $user = auth()->user();
        Log::info('Redirecting user '.$user->name.' to appropriate dashboard');

        if ($user->hasAnyPermission(['access admin dashboard', 'view users', 'manage orders', 'view all orders', 'sync inventory'])) {
            // If user has any admin/staff permission, redirect to admin dashboard
            return redirect()->route('admin.dashboard');
        }

        // Otherwise redirect to regular dashboard
        return redirect()->route('dashboard');
    }
}
