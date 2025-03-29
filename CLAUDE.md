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
- **Run with Specific Environment**: `APP_ENV=testing php artisan test`

## Code Style Guidelines
- **PHP Standard**: PSR-12 (enforced by Laravel Pint)
- **Import Order**: Group imports by type: PHP core, framework, third-party packages, then application classes
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
- **API Timeout**: Default timeout is set to 300 seconds (5 minutes) for large syncs. Configure with `NETSUITE_TIMEOUT` in .env
- **Execution Time**: PHP execution time limit is automatically increased to 300 seconds during sync operations
- **Test NetSuite Connection**: `php artisan netsuite:test-sales` (verifies connection to sales RESTlet)
- **PDF Generation**:
  - **Pick Tickets**: For warehouse orders, use `/orders/{id}/pick-ticket` route
  - **Sales Invoices**: For customer sales, use `/sales/{id}/invoice` route
- **Transaction Types**:
  - The system handles multiple transaction types including **Invoices** and **Credit Memos**
  - Credit Memos must be stored as negative values in the database 
  - Always pass `includeCredits => true` parameter to the RESTlet when retrieving sales data
  - When processing Credit Memos, the `SalesSyncService` automatically converts positive amounts to negative
  - Line items for Credit Memos should also be stored with negative amounts
  - The dashboard and reports handle both transaction types appropriately 
- **Configuration**:
  - Environment variables: 
    - `NETSUITE_ACCOUNT_ID`, `NETSUITE_CONSUMER_KEY`, `NETSUITE_CONSUMER_SECRET`
    - `NETSUITE_TOKEN_ID`, `NETSUITE_TOKEN_SECRET`
    - `NETSUITE_SCRIPT_ID`, `NETSUITE_DEPLOY_ID` (for inventory)
    - `NETSUITE_SALES_DATA_SCRIPT_ID`, `NETSUITE_SALES_DATA_DEPLOY_ID` (for sales)
    - `NETSUITE_TIMEOUT` (API timeout in seconds, default: 300)
  - Integration Model: OAuth 1.0a for authentication with script/deploy IDs to identify RESTlets
- **RESTlet Implementation**:
  - Inventory RESTlet: Returns paginated inventory data from NetSuite
  - Sales RESTlet: Returns all sales data across all pages in a single response
  - Both use SuiteScript 2.1 with saved searches for optimal performance
  - Sales RESTlet expects the following fields in the saved search:
    - Transaction fields: tranid, type, trandate
    - Customer join fields: entityid, altname
    - Item join fields: quantity, amount, salesdescription, itemid

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

- **Data Visualization with Chart.js**:
  - Store chart data in the component using `$chartData` array with JSON-encoded values
  - Create hidden HTML elements to hold current chart data that update with Livewire refreshes
  - Use a global state object (`window.salesAnalyticsState`) to track chart instances
  - Apply proper chart cleanup during re-initialization to prevent memory leaks
  - Handle Livewire update events with multiple delayed initialization attempts
  - For charts with multiple metrics (e.g., amount and quantity), use dual Y-axes
  - Add event listeners to filter controls to trigger chart re-rendering
  - Implement robust error handling with try/catch blocks for all chart operations
  - For date-based charts, use consistent date formatting for readability

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

## Chart.js Integration
When implementing Chart.js visualizations in Livewire components, follow these best practices:

1. **Data Preparation**:
   - Prepare chart data in the PHP component's `render()` method
   - Use JSON encoding for arrays to pass to JavaScript (`$data->toJson()`)
   - Store data in a structured component property like `$chartData`
   - For time series data, ensure consistent date formatting

2. **HTML Structure**:
   - Place chart canvases in appropriate grid layouts (e.g., 2-column for dashboard)
   - Use fixed height containers (e.g., `h-80`) for consistent chart sizing
   - Add hidden data elements that update with Livewire refreshes:
     ```html
     <div class="hidden">
         <div id="chart-data-labels">{{ $chartData["labels"] }}</div>
         <div id="chart-data-values">{{ $chartData["values"] }}</div>
     </div>
     ```

3. **JavaScript Implementation**:
   - Use a global state object to track chart instances across component updates
   - Extract data from hidden DOM elements instead of inline JS variables
   - Implement multiple initialization attempts with different delays
   - Add event listeners for Livewire-specific events:
     ```javascript
     document.addEventListener('livewire:load', initCharts);
     document.addEventListener('livewire:update', initCharts);
     ```
   - For filter controls, add Alpine.js event listeners:
     ```html
     <select wire:model.live="filter" 
             x-on:change="setTimeout(() => initCharts(), 200)">
     ```
   - Always implement complete try/catch blocks around chart initialization
   - Destroy charts properly before recreating them
   - For dual metrics, use dual y-axes configuration

4. **Performance Tips**:
   - Disable animations for better performance (`animation: false`)
   - Use `Math.abs()` for financial data with credit/debit values
   - Limit complex charts to 10-15 data points for readability
   - Add console logging during development to track initialization

## Global Components
The application includes several reusable global components you should use for consistency:

### Blade Components
- **`<x-product-item>`**: Product display with 3 variants (default, compact, list). Use for all product displays.
  - Props: product, variant, showDetails, showQuantity, showPrice, itemKey
  - Example: `<x-product-item :product="$product" variant="compact" />`
- **`<x-product-detail-modal>`**: Product detail modal automatically included in product-item
  - Props: product, modalId
- **`<x-orders-list>`**: Reusable order listing with responsive design
  - Props: orders, isAdmin

### Livewire Components
- **`<livewire:modals.order-detail-modal />`**: Global order detail modal (registered in app layout)
- **`<livewire:modals.transaction-detail-modal />`**: Global transaction detail modal (registered in app layout)
- **`<livewire:cart.add-to-cart />`**: Cart functionality with quantity controls
  - Props: inventory-id, variant, quantity-input-type, show-quantity
- **`<livewire:cart.cart-counter />`**: Cart item counter for navigation

### Global Events
- **showOrderDetail**: Display order details (`Livewire.dispatch('showOrderDetail', [orderId])`)
- **showTransactionDetail**: Display transaction details (`Livewire.dispatch('showTransactionDetail', [transactionId])`)
- **add-to-cart-increment**: Increment cart quantity (`Livewire.dispatch('add-to-cart-increment', { id, change })`)
- **add-to-cart-quantity**: Set cart quantity (`Livewire.dispatch('add-to-cart-quantity', { id, quantity })`)
- **cart-updated**: Listen for cart updates to refresh UI