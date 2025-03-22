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

## Database Commands
- **Run Migrations**: `php artisan migrate` (add `--seed` to seed)
- **Fresh Database**: `php artisan migrate:fresh --seed` (rebuild with seeds)
- **Create Migration**: `php artisan make:migration create_tablename_table`