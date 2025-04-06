<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyCustomerAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware verifies that a user can only access data for their assigned customer
     * unless they have admin/staff permissions to view all customer data.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $customerIdParam = 'id'): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Skip check for users with permission to view all customer data
        if ($user->hasPermissionTo('view netsuite sales data')) {
            return $next($request);
        }

        // Get customer ID from the route parameter
        $requestedCustomerId = $request->route($customerIdParam);

        // If there's no customer ID in the route, continue with the request
        // (this middleware is only for routes with customer ID parameters)
        if (! $requestedCustomerId) {
            return $next($request);
        }

        // If we're checking an entity_id directly (like in sales records)
        if ($customerIdParam === 'entity_id') {
            // Check if the user's customer_number matches the requested entity_id
            if ($user->customer_number && $user->customer_number === $requestedCustomerId) {
                return $next($request);
            }
        }
        // If we're checking a model that has a relationship to entity_id
        else {
            // Get the model name from the route
            $routeName = $request->route()->getName() ?? '';

            // Determine which model to check based on the route
            if (strpos($routeName, 'sales') !== false) {
                $model = \App\Models\Sale::find($requestedCustomerId);
                $entityIdField = 'entity_id';
            } elseif (strpos($routeName, 'orders') !== false) {
                $model = \App\Models\Order::find($requestedCustomerId);
                $entityIdField = 'customer_number';
            } else {
                // Default case, try to find a customer model
                $model = \App\Models\Customer::find($requestedCustomerId);
                $entityIdField = 'entity_id';
            }

            // If model exists and user's customer_number matches the model's entity_id
            if ($model && $user->customer_number && $user->customer_number === $model->{$entityIdField}) {
                return $next($request);
            }
        }

        // If no match or no permission, deny access
        abort(403, 'You are not authorized to access this data');
    }
}
