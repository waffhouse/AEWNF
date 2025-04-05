<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Class UserService
 * 
 * Service class responsible for user-related business logic.
 * This extracts CRUD operations and user management from Livewire components.
 */
class UserService
{
    /**
     * Get users with optional filters
     * 
     * @param array $filters Array of filters to apply ['search' => string, etc.]
     * @param array $options Query options ['with' => array, 'orderBy' => string, 'direction' => string, 'fields' => array]
     * @return Builder The query builder instance
     */
    public function getUsersQuery(array $filters = [], array $options = []): Builder
    {
        $query = User::query();
        
        // Select only specified fields for better performance
        if (isset($options['fields']) && is_array($options['fields'])) {
            // Always include ID
            $fields = $options['fields'];
            if (!in_array('id', $fields)) {
                $fields[] = 'id';
            }
            $query->select($fields);
        } else {
            // Default select - only include necessary fields
            $query->select(['id', 'name', 'email', 'customer_number', 'created_at', 'updated_at', 'last_refreshed_at']);
        }
        
        // Apply optimized eager loading
        if (isset($options['with']) && is_array($options['with'])) {
            // Apply specific field selection for each relation to reduce data transfer
            $eagerLoadWithFields = [];
            foreach ($options['with'] as $relation) {
                if ($relation === 'roles') {
                    $eagerLoadWithFields['roles'] = function($q) {
                        $q->select(['id', 'name']);
                    };
                } else {
                    $eagerLoadWithFields[$relation] = function($q) {
                        // Default eager loading for other relations
                    };
                }
            }
            $query->with($eagerLoadWithFields);
        } else {
            // Default optimized eager loading
            $query->with(['roles' => function($q) {
                $q->select(['id', 'name']);
            }]);
        }
        
        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter with optimized join for better performance
        if (isset($filters['role']) && !empty($filters['role'])) {
            $query->whereHas('roles', function($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }
        
        
        // Apply sorting
        $orderBy = $options['orderBy'] ?? 'name';
        $direction = $options['direction'] ?? 'asc';
        $query->orderBy($orderBy, $direction);
        
        return $query;
    }
    
    /**
     * Get a paginated list of users with optional filters
     * 
     * @param int $perPage Number of items per page
     * @param array $filters Array of filters to apply
     * @param array $options Query options
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 10, array $filters = [], array $options = [])
    {
        return $this->getUsersQuery($filters, $options)->paginate($perPage);
    }
    
    /**
     * Get a user by ID with optional relations and specific fields
     * 
     * @param int $id User ID
     * @param array $with Relations to eager load
     * @param array $fields Fields to select
     * @return User|null
     */
    public function getUserById(int $id, array $with = ['roles'], array $fields = null): ?User
    {
        $query = User::query();
        
        // Select only specified fields or default select
        if ($fields) {
            // Always include ID
            if (!in_array('id', $fields)) {
                $fields[] = 'id';
            }
            $query->select($fields);
        }
        
        // Optimize eager loading by selecting specific relation fields
        $eagerLoadWithFields = [];
        foreach ($with as $relation) {
            if ($relation === 'roles') {
                $eagerLoadWithFields['roles'] = function($query) {
                    $query->select(['id', 'name']);
                };
            } else {
                $eagerLoadWithFields[$relation] = function($query) {
                    // Default select for other relations
                };
            }
        }
        
        if (!empty($eagerLoadWithFields)) {
            $query->with($eagerLoadWithFields);
        }
        
        return $query->find($id);
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User data
     * @param string|null $role Role name to assign
     * @return User The created user
     * @throws ValidationException If validation fails
     * @throws AuthorizationException If user doesn't have permission
     */
    public function createUser(array $userData, ?string $role = null): User
    {
        Log::info('Creating new user: ' . ($userData['email'] ?? 'No email provided'));
        
        // Extract role from the userData if not explicitly provided
        if (!$role && isset($userData['role'])) {
            $role = $userData['role'];
            unset($userData['role']);
        }
        
        // Hash the password if it's provided in plain text
        if (isset($userData['password']) && !Hash::isHashed($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }
        
        // Handle customer number for customer roles
        if (isset($role)) {
            // Always clear customer_number for non-customer roles
            if (strpos(strtolower($role), 'customer') === false) {
                $userData['customer_number'] = null; // Remove customer number for non-customer roles
            } elseif (empty($userData['customer_number'])) {
                // For customer roles, ensure customer_number is required
                throw new ValidationException(
                    new \Illuminate\Support\MessageBag([
                        'customer_number' => ['A customer number is required for customer roles.']
                    ])
                );
            }
        }
        
        // Create the user
        $user = User::create($userData);
        
        // Assign role if provided
        if ($role) {
            $user->assignRole($role);
        }
        
        return $user;
    }
    
    /**
     * Update an existing user
     * 
     * @param int $userId User ID
     * @param array $userData User data
     * @param string|null $role Role name to assign
     * @return User The updated user
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user not found
     */
    public function updateUser(int $userId, array $userData, ?string $role = null): User
    {
        Log::info('Updating user ID: ' . $userId);
        
        $user = User::findOrFail($userId);
        
        // Extract role from the userData if not explicitly provided
        if (!$role && isset($userData['role'])) {
            $role = $userData['role'];
            unset($userData['role']);
        }
        
        // Hash the password if it's provided in plain text
        if (isset($userData['password']) && !empty($userData['password'])) {
            if (!Hash::isHashed($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            }
        } else {
            // Don't update password if not provided or empty
            unset($userData['password']);
        }
        
        // Handle customer number for customer roles
        if (isset($role)) {
            // Always clear customer_number for non-customer roles
            if (strpos(strtolower($role), 'customer') === false) {
                $userData['customer_number'] = null; // Remove customer number for non-customer roles
            } elseif (empty($userData['customer_number'])) {
                // For customer roles, ensure customer_number is required
                throw new ValidationException(
                    new \Illuminate\Support\MessageBag([
                        'customer_number' => ['A customer number is required for customer roles.']
                    ])
                );
            }
        }
        
        // Update user data
        $user->update($userData);
        
        // Update role if provided
        if ($role) {
            $user->syncRoles([$role]);
        }
        
        return $user;
    }
    
    /**
     * Delete a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     * @throws \Exception If deletion fails
     */
    public function deleteUser(int $userId): bool
    {
        Log::info('Deleting user ID: ' . $userId);
        
        $user = User::findOrFail($userId);
        
        try {
            // Delete related models if needed
            // e.g., $user->someRelation()->delete();
            
            return (bool) $user->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cached roles collection to avoid redundant queries
     */
    protected static $rolesCache = null;
    
    /**
     * Get all available roles with caching
     * 
     * @param bool $forceRefresh Force a refresh of the cache
     * @return Collection
     */
    public function getAllRoles(bool $forceRefresh = false): Collection
    {
        // Refresh cache if needed or forced
        if (self::$rolesCache === null || $forceRefresh) {
            // Select only the fields we need to reduce data transfer
            self::$rolesCache = Role::select(['id', 'name'])
                ->orderBy('name')
                ->get();
        }
        
        return self::$rolesCache;
    }
    
    /**
     * Check if a user exists with the given email
     * 
     * @param string $email Email to check
     * @param int|null $exceptUserId User ID to exclude from the check
     * @return bool
     */
    public function emailExists(string $email, ?int $exceptUserId = null): bool
    {
        $query = User::where('email', $email);
        
        if ($exceptUserId) {
            $query->where('id', '!=', $exceptUserId);
        }
        
        return $query->exists();
    }
    
    /**
     * Check if a customer number is already in use
     * 
     * @param string $customerNumber Customer number to check
     * @param int|null $exceptUserId User ID to exclude from the check
     * @return bool
     */
    public function customerNumberExists(string $customerNumber, ?int $exceptUserId = null): bool
    {
        $query = User::where('customer_number', $customerNumber);
        
        if ($exceptUserId) {
            $query->where('id', '!=', $exceptUserId);
        }
        
        return $query->exists();
    }
    
    /**
     * Get validation rules for creating a user
     * 
     * @return array
     */
    public function getCreateRules(): array
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name',
            'customer_number' => 'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number',
        ];
    }
    
    /**
     * Get validation rules for updating a user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUpdateRules(int $userId): array
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => 'nullable|min:8',
            'role' => 'required|exists:roles,name',
            'customer_number' => 'nullable|string|max:10|regex:/^\d{4}$/|unique:users,customer_number,' . $userId,
        ];
    }
    
    /**
     * Get custom validation messages
     * 
     * @return array
     */
    public function getValidationMessages(): array
    {
        return [
            'customer_number.regex' => 'The customer number must be a 4-digit number.',
            'customer_number.unique' => 'This customer number is already in use. Please assign a different number.',
            'role.exists' => 'The selected role does not exist.',
        ];
    }
}