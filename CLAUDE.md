# Laravel Project Guide (A&E Wholesale of North Florida)

## Build/Development Commands
- **Start Server**: `php artisan serve`
- **All-in-One Dev**: `composer run dev` (runs server, queue, logs, and vite concurrently)
- **Asset Compilation**: `npm run dev` (development) or `npm run build` (production)
- **Code Linting**: `./vendor/bin/pint` (Laravel Pint for PSR-12 formatting)
- **Clear Cache**: `php artisan cache:clear && php artisan view:clear && php artisan route:clear`
- **Debug Mode**: Set `APP_DEBUG=true` in .env for detailed error messages

## Testing Commands
- **Run All Tests**: `php artisan test` or `./vendor/bin/phpunit`
- **Run Single Test**: `php artisan test --filter TestName`
- **Test Specific File**: `php artisan test tests/Feature/Auth/RegistrationTest.php`
- **Test Specific Method**: `php artisan test tests/Feature/Auth/RegistrationTest.php::test_new_users_can_register`
- **With Coverage**: `php artisan test --coverage`
- **Setup Test Data**: Use `seedTestDatabase()` method from TestCase for standard test data seeding

## Code Style Guidelines
- **PHP Standard**: PSR-12 (enforced by Laravel Pint)
- **Naming Conventions**:
  - Classes/Interfaces: PascalCase (`UserController`, `AppServiceProvider`)
  - Methods/Properties/Variables: camelCase (`getUserById`, `$customerNumber`)
  - Blade/Livewire Files: kebab-case (`user-management.blade.php`)
- **Type Annotations**: Use PHP 8.2+ typed properties and PHPDoc annotations (`@var`, `@return`, `@param`)
- **Models**: Define relationships, fillable properties, and casts in dedicated methods
- **Error Handling**: Use try/catch with appropriate logging, never expose exceptions to users
- **Permission Enforcement**: Implement at component methods, render methods, and router middleware
- **Livewire Components**: Use form validation traits and appropriate lifecycle hooks
- **Testing**: Use custom assertions `assertSeeLivewire` and `assertSeeVolt` for component testing

## Database Commands
- **Run Migrations**: `php artisan migrate` (add `--seed` to seed)
- **Fresh Database**: `php artisan migrate:fresh --seed` (rebuild with seeds)
- **Create Migration**: `php artisan make:migration create_tablename_table`

## NetSuite Integration
- **Inventory Sync**: `php artisan netsuite:sync-inventory` (syncs inventory items)
- **Sales Data Sync**: `php artisan netsuite:sync-sales` (syncs sales transactions)
  - Optional parameters: `--date=YYYY-MM-DD` to filter by specific date
  - Optional parameters: `--size=1000` to set internal page size (default: 1000)
- **Test NetSuite Connection**: `php artisan netsuite:test-sales` (verifies connection to sales RESTlet)
- **PDF Generation**:
  - **Pick Tickets**: For warehouse orders, use `/orders/{id}/pick-ticket` route
  - **Sales Invoices**: For customer sales, use `/sales/{id}/invoice` route
- **Configuration**:
  - Environment variables: 
    - `NETSUITE_ACCOUNT_ID`, `NETSUITE_CONSUMER_KEY`, `NETSUITE_CONSUMER_SECRET`
    - `NETSUITE_TOKEN_ID`, `NETSUITE_TOKEN_SECRET`
    - `NETSUITE_SCRIPT_ID`, `NETSUITE_DEPLOY_ID` (for inventory)
    - `NETSUITE_SALES_DATA_SCRIPT_ID`, `NETSUITE_SALES_DATA_DEPLOY_ID` (for sales)
  - Integration Model: OAuth 1.0a for authentication with script/deploy IDs to identify RESTlets
- **RESTlet Implementation**:
  - Inventory RESTlet: Returns paginated inventory data from NetSuite
  - Sales RESTlet: Returns all sales data across all pages in a single response
  - Both use SuiteScript 2.1 with saved searches for optimal performance

## Dashboard Design Patterns
- **Admin Dashboard Structure**: 
  - Tab-based navigation with tab state preserved in URL hash
  - Permission-based tab visibility controlled by `userCanAccessTab()` method
  - Component isolation with each tab loading its own Livewire component
  - Central error/success message handling with Alpine.js

- **Synchronization Dashboards**:
  - Use statistics-based dashboards for data syncing features rather than full data tables
  - Include last sync time, current data summary, and sync controls
  - Display detailed sync results including created/updated/deleted counts
  - Show aggregate statistics by relevant categories (e.g., transaction types for sales)
  - Use color coding to distinguish between different metric types
  - Implement a `loadLastSyncInfo()` method to update statistics after operations

- **Component Communication**:
  - For dashboard events, use Livewire's `$dispatch()` method with specific event names
  - When component communication is causing issues, simplify by having each component
    refresh itself rather than trying to coordinate updates
  - For user table syncing and updates, use targeted events like `user-created` or `user-updated`
  - For operations that modify large datasets, prefer reloading statistics instead of 
    trying to update table contents in real-time

## UI Components
- **Modals**: For fixed headers with scrollable content, use the pattern:
  ```html
  <div class="flex flex-col max-h-[90vh]">
    <div class="p-6 border-b"><!-- Fixed header content --></div>
    <div class="p-6 flex-1 overflow-y-auto"><!-- Scrollable content --></div>
  </div>
  ```
- **Tables**: For data tables, use the component `<x-components.tables.sortable-table>` or vanilla HTML with TailwindCSS classes
- **Forms**: For admin forms, use Livewire with real-time validation
- **InfiniteScrollable**: For large datasets, use the `InfiniteScrollable` trait with Intersection Observer API
- **PDF Documents**: For PDF generation, use Barryvdh/DomPDF with these guidelines:
  - Use table-based layouts for consistent positioning across elements
  - For headers with logo and document details, use a two-column table layout
  - Color scheme: black text for headings, red accents only for key elements
  - Use consistent fonts, sizing, and spacing throughout
  - Include proper document metadata (title, number, date)
  - For financial documents, right-align monetary values