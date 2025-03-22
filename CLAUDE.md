# Laravel Project Guide (A&E Wholesale of North Florida)

## Build/Development Commands
- **Start Server**: `php artisan serve`
- **All-in-One Dev**: `composer run dev` (runs server, queue, logs, and vite concurrently)
- **Asset Compilation**: `npm run dev` (development) or `npm run build` (production)
- **Code Linting**: `./vendor/bin/pint` (Laravel Pint for PSR-12 formatting)
- **Clear Cache**: `php artisan cache:clear && php artisan view:clear && php artisan route:clear`

## Testing Commands
- **Run All Tests**: `php artisan test` or `./vendor/bin/phpunit`
- **Run Single Test**: `php artisan test --filter TestName`
- **Test Specific File**: `php artisan test tests/Feature/Auth/RegistrationTest.php`
- **Test Specific Method**: `php artisan test tests/Feature/Auth/RegistrationTest.php::test_new_users_can_register`
- **With Coverage**: `php artisan test --coverage`

## Code Style Guidelines
- **PHP Standard**: PSR-12 (enforced by Laravel Pint)
- **Naming Conventions**:
  - Classes/Interfaces: PascalCase (`UserController`, `AppServiceProvider`)
  - Methods/Properties/Variables: camelCase (`getUserById`, `$customerNumber`)
  - Blade/Livewire Files: kebab-case (`user-management.blade.php`)
- **Imports**: Group by type (PHP core, Laravel, third-party, App), alphabetical within groups
- **Models**: Use typed properties, define relationships, casts, and fillable properties
- **Error Handling**: Use try/catch with appropriate logging, never expose exceptions to users
- **Permission Enforcement**: Implement at component methods, render methods, blade templates, and router middleware
- **UI Components**: Follow existing patterns for modals, tables, cards, and responsive designs
- **Livewire Components**: Use form validation traits and appropriate lifecycle hooks

## Styling Guide
- **Color Palette**: The project uses a customized Tailwind configuration
  - **Important**: `indigo` colors are remapped to red shades (see tailwind.config.js)
  - Use `text-red-400` for accent text (replaces what would typically be blue/indigo in standard Tailwind)
  - Use `bg-gray-900` for dark backgrounds (headers, footers)
  - Use `text-gray-100` for light text on dark backgrounds
- **Components**:
  - Modals: Use the standard modal structure with a solid color header
  - Cards: Use `rounded-lg`, `shadow-sm`, and `border border-gray-200` for consistency
  - Buttons: Use `rounded-md` with appropriate color classes based on action type
  - Accordions: Use Alpine.js with parent state management (`expandedId` pattern) to ensure only one accordion is open at a time
- **Responsive Design**:
  - Always implement both desktop and mobile views
  - Use accordion patterns for complex data display on smaller screens
  - Card-based layouts for mobile, table-based layouts for desktop
- **Interactive Elements**:
  - Use Alpine.js for client-side interactions
  - Reset accordion states after status changes with `@order-status-updated.window` listener
  - Implement "click outside" behavior for dropdowns and modals

## Database Commands
- **Run Migrations**: `php artisan migrate` (add `--seed` to seed)
- **Fresh Database**: `php artisan migrate:fresh --seed` (rebuild with seeds)
- **Create Migration**: `php artisan make:migration create_tablename_table`