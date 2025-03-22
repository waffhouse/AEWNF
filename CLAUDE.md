# Laravel Project Guide (A&E Wholesale of North Florida)

## Environment Setup
- **Development**: Uses SQLite by default (no setup required)
- **Production**: Uses MySQL database
  - Ensure MySQL is installed and configured on the production server
  - Update `.env` to set `DB_CONNECTION=mysql` and other MySQL credentials
  - Run `php artisan migrate` to create tables in MySQL

## Scheduled Tasks (Laravel 12)
- Tasks are defined in `routes/console.php` using the `Schedule` facade
- Check configured tasks with `php artisan schedule:list`
- **Development**: Run `php artisan schedule:work` to run the scheduler in foreground
- **Production**: Add this single cron entry to run all scheduled tasks:
  ```
  * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
  ```
- Current scheduled tasks:
  - NetSuite inventory sync (hourly)

## Development Commands
- **Start Server**: `php artisan serve`
- **All-in-One Dev**: `composer run dev` (runs server, queue, logs, and vite concurrently)
- **Asset Compilation**: `npm run dev` (development) or `npm run build` (production)
- **Code Linting**: `./vendor/bin/pint` (Laravel Pint for PSR-12 formatting)
- **Clear Cache**: `php artisan cache:clear && php artisan view:clear && php artisan route:clear`
- **Queue Worker**: `php artisan queue:work` (process jobs) or `php artisan queue:listen --tries=1` (development)
- **Logging/Debug**: `php artisan pail --timeout=0` (Laravel Pail for real-time logs)

## Testing Commands
- **Run All Tests**: `php artisan test` or `./vendor/bin/phpunit`
- **Run Single Test**: `php artisan test --filter TestName`
- **Test by Suite**: `php artisan test --testsuite=Feature` or `--testsuite=Unit`
- **Test Specific File**: `php artisan test tests/Feature/Auth/RegistrationTest.php`
- **Test Specific Method**: `php artisan test tests/Feature/Auth/RegistrationTest.php::test_new_users_can_register`

## Database Commands
- **Run Migrations**: `php artisan migrate` (add `--seed` to seed)
- **Run Seeders**: `php artisan db:seed` or specific seeder `--class=RolesAndPermissionsSeeder`
- **Fresh Database**: `php artisan migrate:fresh --seed` (rebuild with seeds)

## Code Style Guidelines
- **PHP Standard**: PSR-12 (enforced by Laravel Pint)
- **Naming Conventions**:
  - Classes/Interfaces: PascalCase (`UserController`, `AppServiceProvider`)
  - Methods/Properties/Variables: camelCase (`getUserById`, `$customerNumber`)
  - Blade/Livewire Files: kebab-case (`user-management.blade.php`)
- **Imports**: Group by type (PHP core, Laravel, third-party, App), alphabetical within groups
- **Livewire Components**: Use attributes approach (new in v3)
- **Models**: Define relationships, casts, fillable properties, and use typed properties
- **Error Handling**: Use try/catch with appropriate logging, never expose exceptions to users

## Role and Permission System
- **Package**: Spatie Laravel Permission package
- **Roles**:
  - `admin`: Full access to all features including user, role, and permission management
  - `staff`: Limited access to view users and inventory data
  - `florida customer`: Access to catalog, Florida-specific items, and unrestricted items
  - `georgia customer`: Access to catalog, Georgia-specific items, and unrestricted items
- **Permissions**:
  - User management: `view users`, `create users`, `edit users`, `delete users`
  - Role management: `manage roles`  
  - Permission management: `manage permissions`
  - Admin dashboard: `access admin dashboard`
  - Inventory: `sync inventory`
  - Catalog and sales: `view catalog`, `view sales history`
  - Item visibility: `view unrestricted items`, `view florida items`, `view georgia items`
  - Shopping cart: `add to cart`, `place orders`, `view own orders`, `view all orders`, `manage orders`
- **Permission Enforcement**:
  - IMPORTANT: For complete permission enforcement, checks must be implemented at MULTIPLE levels:
    1. **Livewire Component Methods** (e.g., `app/Livewire/Admin/UserManagement.php`): Check permissions in each action method using `hasPermissionTo()` before allowing actions
    2. **Component Render Methods**: Check permissions in `render()` methods to restrict access to entire components
    3. **Blade Templates**: Use `@can` directives to conditionally show/hide UI elements
    4. **Router Middleware**: Apply permission middleware to routes (`middleware(['permission:view users'])`)
  - **Critical Components for Permission Enforcement**:
    - `app/Livewire/Admin/Dashboard.php`: Main dashboard with role and permission management
    - `app/Livewire/Admin/UserManagement.php`: User management component - requires permission checks for all CRUD operations
    - `resources/views/livewire/admin/user-management.blade.php`: UI-level access control for user management
    - Admin users always have access to all features regardless of specific permissions
- **Admin Dashboard**:
  - Single-page tabbed interface for efficient administration
  - Tab navigation with count badges and URL hash-based persistence of tab selection
  - Responsive tables with mobile optimizations (column hiding, inline information)
  - Modal-based forms for create/edit operations with immediate feedback
  - Delete operations with clear confirmation and validation messages
  - **User Management Tab**:
    - Password field toggle for visibility
    - Role-based customer number field (only shown for customer role)
    - Email uniqueness validation with clear error messages
  - **Role Management Tab**:
    - Create/edit roles with permission selection
    - Validation to prevent deletion of roles assigned to users
    - Validation to prevent deletion of roles with assigned permissions
    - Clear error messages listing affected entities
  - **Permission Management Tab**:
    - Create/edit permissions with role assignment
    - Validation to prevent deletion of permissions assigned to roles
    - Error messages showing which roles are using a permission
  - **Inventory Sync Tab**:
    - One-click NetSuite inventory synchronization
    - Real-time feedback during sync operations
    - Detailed statistics display (created, updated, deleted items)
    - Sync history tracking with timestamps
  - **Order Management Tab**:
    - View and manage customer orders
    - Filter by status (pending, completed, cancelled)
    - Status change functionality with confirmation
    - Detailed order view with line items
  - Success/error toast notifications with automatic dismissal

## Shopping Cart and Order System
- **Key Models**:
  - `Cart`: User's shopping cart with items relationship and total calculations
  - `CartItem`: Individual items in the cart with quantity, price
  - `Order`: Customer orders with status management
  - `OrderItem`: Line items within orders with product details
- **Components**:
  - `AddToCart`: Button/counter for adding products to cart
  - `CartCounter`: Real-time cart indicator with count and total
  - `CartPage`: Full shopping cart view with item management
  - `OrderManagement`: Admin interface for managing orders
- **Permission Structure**:
  - `add to cart`: Required to add products to cart
  - `place orders`: Required to complete checkout
  - `view own orders`: For customers to see their order history
  - `view all orders`: For staff to view any customer's orders
  - `manage orders`: For updating order statuses
- **User Experience**:
  - State-specific pricing based on user permissions
  - Responsive design for desktop and mobile
  - Real-time cart updates and notifications
  - Permission-based UI elements
  - Order history tracking for customers

## Application Structure
- Livewire components in `app/Livewire/` (matching `resources/views/livewire/`)
- Controllers in `app/Http/Controllers/`
- Routes in `routes/web.php` and `routes/auth.php`
- Blade components in `resources/views/components/`

## UI/UX Design Patterns
- **Design System**:
  - Standardized typography: `text-xl font-semibold` for page titles
  - Consistent padding: `p-6` for all content containers
  - Uniform card styling with hover effects and shadow transitions
  - Standardized form controls and interactive elements
  - Light-mode pagination across all listing pages
  - Enhanced mobile navigation with persistent cart icon in header
  - Dual-display cart counters showing both item count and total price
  - Smooth scroll-to-top button for improved catalog browsing

- **Responsive Design**:
  - Mobile-first approach using Tailwind's responsive utilities
  - Collapsible sections for mobile screens to conserve space
  - Optimized table layouts that adapt to screen sizes
  - Sticky filter menu for mobile product browsing
  - Wrapping navigation tabs for small screens instead of dropdown selectors
  - Properly cloak menus during page transitions with `x-cloak`

- **Interface Efficiency**:
  - Tabbed interfaces for content-dense sections (admin dashboard)
  - Card-based layouts with consistent styling for visual data presentation 
  - Compact stats indicators for quick system overviews
  - URL hash-based state persistence for tab selections
  
- **Data Visualization**:
  - Color-coded badges for roles, permissions, and state availability
  - Clear visual hierarchy with standardized spacing
  - Information truncation with "+X more" patterns for dense data
  - Consistent iconography for actions (edit, delete, add, etc.)
  
- **Alpine.js Interactions**:
  - Tabbed interfaces managed via Alpine state variables
  - Responsive navigation behaviors with mobile menu state management
  - Collapsible section toggling with smooth transitions
  - Filter modals and sticky navigation for mobile users
  - No page reloads required for UI state changes
  - Proper state reset during Livewire navigation events
  - Event listeners for handling transitions between pages

- **Permission-Based UI Components**:
  - **Dynamic Dashboard Cards**: Auto-generated based on user permissions
  - **Users List vs. User Management**: Separate view-only and management interfaces
  - **Consistent Table Components**: Same styling across view-only and administrative interfaces
  - **State-Filtered Catalog**: Different product visibility based on state selection

## Dashboard and User Interface
- **Modular Admin Dashboard** (resources/views/livewire/admin/dashboard.blade.php):
  - Tab-based interface for efficient administration
  - Mobile-friendly wrapped tabs that maintain visual consistency across screen sizes
  - Each tab loads a separate Livewire component for modularity
  - Components follow Single Responsibility Principle:
    - UserManagement - handles user CRUD operations
    - RoleManagement - handles role CRUD operations
    - PermissionManagement - handles permission CRUD operations
    - InventorySync - handles NetSuite inventory synchronization
    - OrderManagement - handles viewing and managing customer orders
  - Consistent modal-based UI across all components
  - Components share base AdminComponent class and AdminAuthorization trait
  - Count badges showing total users, roles, permissions, and orders
  - Hash-based URL for preserving tab state between page loads
  - Simple state management with minimal dependencies
  - Alpine.js integration with proper cleanup for smooth transitions
  - Responsive design with proper state management during page transitions

- **User Management vs. Users List**:
  - **Users List** (`app/Livewire/Admin/UsersList.php`): 
    - View-only interface for users with 'view users' permission
    - No edit/delete functionality
    - Search functionality by name, email, or customer number
    - Displays user roles with color-coded badges
    - Accessible via primary navigation and dashboard for staff users
  - **User Management** (`app/Livewire/Admin/UserManagement.php`):
    - Full CRUD functionality for user records
    - Permission-based controls for create, edit, delete operations
    - Uses modals for create/edit operations
    - Available only to admin users with appropriate permissions
    - Integrated into the tabbed admin dashboard

## Product Catalog
- Located in `app/Livewire/Inventory/Catalog.php` and `resources/views/livewire/inventory/catalog.blade.php`
- **Key Features**:
  - Responsive design that works on desktop, tablet, and mobile
  - Collapsible filter section on mobile devices to save space
  - URL-based filtering that persists across page loads
  - Catalog accessible to general public, pricing only for authenticated users
  - Permission-based display of pricing information based on state-specific permissions
  
- **Filtering Capabilities**:
  - Persistent search bar outside filter section for easy access
  - Brand filter: Select products from specific manufacturers
  - Category filter: Filter by product categories (Beverages, Tobacco, etc.)
  - Search functionality: Search across SKU, brand, and product descriptions
  - Active filter badges: Clear visual indicators showing current filters
  - Filter counter badge showing number of active filters
  - Clear filters button: Quickly reset all filters
  - Availability information panels showing state-specific access based on user role
  
- **Product Card Design**:
  - Clean, card-based layout with responsive grid system
  - Description prominently displayed at the top for quick identification
  - Branded information clearly labeled (Brand, Category, Item #)
  - State availability badges (Florida Only, Georgia Only, All States)
  - Stock status indicators (In Stock, Out of Stock)
  - Price levels organized by state (Florida, Georgia, Bulk Discount)
  - Login prompt for non-authenticated users instead of pricing
  - Quantity selector for adding items to cart with minimum/maximum enforcement
  - Visual indicators when items are already in cart
  - Add to cart button with dynamic state updates and quantity display
  
- **Permission System**:
  - `view catalog`: Basic permission to browse products (available to all)
  - `view unrestricted items`: Required permission to see items available in all states
  - `view florida items`: Required permission to see Florida-specific items
  - `view georgia items`: Required permission to see Georgia-specific items
  - Guest users see "Login to see pricing" with sign-in link
  - Logged-in users see pricing based on their state-specific permissions
  - Role-based visibility ('florida customer' role only sees Florida and unrestricted items)
  
- **User Experience Enhancements**:
  - Infinite scroll loading for product catalog with loading indicators
  - Price unavailability indicators (N/A or Not Available)
  - Hover effects on cards for better interaction feedback
  - Minimal loading with efficient database queries
  - Consistent styling across all parts of the application
  - Return to top button appears when scrolled down page
  - Automatic scroll to top when applying filters for better context awareness
  - Persistent cart icon in mobile header for immediate access from any screen
  - Cart counter with real-time item count and total price display

## NetSuite Integration
- Configuration stored in `config/netsuite.php`
- Service located at `app/Services/NetSuiteService.php`
- **Authentication**: OAuth 1.0a with HMAC-SHA256 signature
- **Inventory Sync Service**:
  - Located at `app/Services/InventorySyncService.php`
  - Responsible for syncing data between NetSuite and local database
  - Handles CRUD operations for inventory items:
    - Creates new inventory items
    - Updates existing inventory items
    - Deletes items that are no longer active in NetSuite
  - Maintains data integrity through transaction-based processing
- **Sync Options**:
  - **Automated**: Scheduled to run hourly using Laravel's scheduler in `routes/console.php`
  - **Manual (Admin Dashboard)**: Admin users can trigger syncs manually from the Inventory Sync tab in the Admin Dashboard
  - **Command Line**: Run via `php artisan netsuite:sync-inventory` command
- **Production Setup**:
  - Run `php artisan schedule:list` to verify the scheduled task is registered
  - Add this single cron entry to handle all scheduled tasks:
    ```
    * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
    ```
  - For development/testing, use `php artisan schedule:work` to run in foreground
- **Data Handling**:
  - Stores inventory in the database (SQLite in dev, MySQL in production)
  - Inventory model located at `app/Models/Inventory.php`
  - Main fields: netsuite_id, sku, brand, class, description, state, quantity, prices
  - Includes helper methods for state availability (isAvailableInFlorida, isAvailableInGeorgia)
  - State field: empty string ('') or NULL means available in all states, 'Florida' or 'Georgia' for state-specific items
- **Admin Dashboard Integration**:
  - Located in the "Inventory Sync" tab of the Admin Dashboard
  - Provides a manual sync button with real-time feedback
  - Displays detailed sync results (total, created, updated, failed, deleted)
  - Shows sync duration and last completed sync time
- **Troubleshooting**: 
  - If you see a TypeError about `reset()`, clear cache with `php artisan view:clear`
  - Check sync logs in `storage/logs/netsuite-sync.log` and `storage/logs/laravel.log`
  - View sync statistics with database query: `SELECT * FROM inventories ORDER BY last_synced_at DESC LIMIT 10;`
  - Monitor sync frequency with `php artisan schedule:list`

## Mobile UI Troubleshooting
- **Menu Flashing During Navigation**: If the mobile menu flashes during page transitions:
  - Ensure `x-cloak` is applied to all menu elements that should be hidden initially
  - Add event listeners in app.js for 'livewire:navigating' to properly reset Alpine.js states
  - Add close menu click handlers (@click="open = false") to navigation links
  
- **Form Validation Errors**:
  - Property errors (like "Property not found") usually indicate mismatched variable names
  - In UserManagement.php, ensure property names (`$userRole`) match what's used in resetForm()
  - Check for typos between Livewire component properties and blade template bindings
  
- **Admin Dashboard Mobile Experience**:
  - Use mobile-friendly tab designs with flex-wrap instead of dropdown selectors
  - Test on multiple device sizes to ensure proper wrapping behavior
  - Verify hash-based navigation works properly on mobile devices

- **Filter Badge Display Issues**:
  - The state filter badge should only appear when explicitly selected by user
  - Always use `$this->isStateFilterActive()` method in templates instead of `$state !== 'all' && $filtersApplied`
  - In filter counter badges, check `$this->isStateFilterActive()` rather than direct property comparisons
  - The `isStateFilterActive()` method properly maintains state of explicitly selected filters
  - Filter badge displays are synchronized across both regular and mobile interfaces