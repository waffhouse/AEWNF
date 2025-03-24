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
- **Imports**: Group by type (PHP core, Laravel, third-party, App), alphabetical within groups
- **Models**: Use typed properties, define relationships, casts, and fillable properties
- **Error Handling**: Use try/catch with appropriate logging, never expose exceptions to users
- **Permission Enforcement**: Implement at component methods, render methods, and router middleware
- **Livewire Components**: Use form validation traits and appropriate lifecycle hooks
- **Testing**: Use custom assertions `assertSeeLivewire` and `assertSeeVolt` for component testing

## UI/UX Guidelines
- **Color Palette**: Primary: `text-red-500`/`bg-red-500`, Dark: `bg-gray-900`, Light text: `text-gray-100`
- **Components**: Consistent styling for cards (`rounded-lg shadow-sm border-gray-200`), modals, buttons
- **Responsive Design**: Always implement mobile (cards) and desktop (tables) views
- **Interactive Elements**: Use Alpine.js for client-side interactions and parent state management

## Database Commands
- **Run Migrations**: `php artisan migrate` (add `--seed` to seed)
- **Fresh Database**: `php artisan migrate:fresh --seed` (rebuild with seeds)
- **Create Migration**: `php artisan make:migration create_tablename_table`