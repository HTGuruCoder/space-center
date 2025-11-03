# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Livewire, multi-language support, and role-based permissions. The project uses:
- **Backend**: Laravel 12 (PHP 8.2+) - https://laravel.com/docs/12.x/
- **Frontend**: Livewire 3, TailwindCSS 4, DaisyUI, Mary UI components
- **Testing**: Pest PHP
- **Build Tool**: Vite with Laravel plugin

### Key Packages & Documentation

**UI Stack** (layered architecture):
- `livewire/livewire` - Full-stack framework for Laravel - https://livewire.laravel.com/docs
- `robsontenorio/mary` - Livewire/Laravel bridge for DaisyUI components - https://mary-ui.com/docs/installation
- `daisyui` - TailwindCSS component library (used via Mary UI) - https://daisyui.com/docs/intro/
- `tailwindcss` - Utility-first CSS framework (under the hood) - https://tailwindcss.com/docs
- `power-components/livewire-powergrid` - DataTable component for Livewire - https://livewire-powergrid.com/get-started/introduction.html

**Icons**:
- `blade-ui-kit/blade-icons` + `postare/blade-mdi` - Google Material Design Icons for Blade
- Usage: https://mary-ui.com/docs/components/icon

**Permissions**:
- `spatie/laravel-permission` - Role and permission management - https://spatie.be/docs/laravel-permission/v6/introduction

**Localization**:
- `mcamara/laravel-localization` - Multi-language routing - https://github.com/mcamara/laravel-localization
- `laravel-lang/common` - Laravel translations - https://laravel-lang.com/introduction.html
- `kkomelin/laravel-translatable-string-exporter` - Extract translatable strings - https://github.com/kkomelin/laravel-translatable-string-exporter

## Development Commands

### Initial Setup
```bash
composer setup
```
This runs: composer install, creates .env from .env.example, generates app key, runs migrations, npm install, and npm run build.

### Development Server
```bash
composer dev
```
This starts 4 concurrent processes:
- Laravel development server (http://localhost:8000)
- Queue listener
- Laravel Pail (real-time logs)
- Vite dev server (hot module replacement)

Alternatively, run services individually:
```bash
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Frontend Build
```bash
npm run build   # Production build
npm run dev     # Development with HMR
```

### Testing
```bash
composer test              # Run all tests
php artisan test          # Alternative command
./vendor/bin/pest         # Run Pest directly
./vendor/bin/pest --filter TestName  # Run specific test
```

Tests use Pest and are located in `tests/Feature` and `tests/Unit`. The test environment uses SQLite in-memory database.

### Code Quality
```bash
./vendor/bin/pint         # Format code (Laravel Pint)
```

### Other Useful Commands
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Fresh database with seeders
php artisan tinker               # REPL
php artisan route:list           # List all routes
php artisan config:clear         # Clear config cache
php artisan cache:clear          # Clear application cache
php artisan view:clear           # Clear compiled views
```

## Architecture

### Models
- **UUID Primary Keys**: Models use UUID instead of auto-incrementing integers (via `HasUuids` trait)
- **User Model**: Located in `app/Models/User.php`, uses HasFactory, Notifiable, HasUuids, HasRoles, and HasCreator traits
- **Permission System**: Custom `Permission` and `Role` models extend Spatie's permission package

### Traits

**HasCreator** (`app/Traits/HasCreator.php`):
- Automatically tracks which user created a record
- Sets `created_by` field to authenticated user's ID when creating records
- Only sets if user is logged in and field is not already set
- Provides `creator()` relationship method to get the creator User

**Using HasCreator on Models**:
```php
use App\Traits\HasCreator;

class Post extends Model
{
    use HasCreator;

    protected $fillable = ['title', 'content', 'created_by'];
}

// Create record (created_by automatically set)
$post = Post::create(['title' => 'My Post', 'content' => 'Content']);

// Access creator
$creator = $post->creator; // Returns User model
```

**Migration Pattern for created_by**:
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    // ... other columns
    $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

**User Model Fields**:
- `first_name`, `last_name` (required)
- `email` (required, unique)
- `phone_number` (required)
- `timezone` (required, default: UTC)
- `currency_code` (required, 3 chars)
- `picture_url`, `birth_date`, `country_code` (nullable, ISO 2 code), `bank_name`, `bank_account_number` (nullable)
- `created_by` (nullable, foreign UUID to users table)
- Accessor: `$user->full_name` returns "FirstName LastName"

### Localization
- **Route Localization**: All web routes are prefixed with locale (e.g., `/en/...`, `/es/...`)
- **Middleware Stack**: Routes use `localeCookieRedirect`, `localizationRedirect`, and `localeViewPath` middleware
- **Configuration**: `config/laravellocalization.php` defines supported locales
- **Current Setup**: English (en) and Spanish (es) are enabled by default
- **Locale Detection**: Uses browser locale detection via `codezero/browser-locale`

### Enums & Utilities (Data Management)

The application includes enums and utilities for managing timezones, countries, and currencies with full localization support.

**Timezone Utility** (`app/Utils/Timezone.php`):
- Not an enum (hundreds of IANA timezone identifiers exist)
- Provides methods to work with all IANA timezones with localization
- All timezone names are translated via `lang/en/timezones.php` and `lang/es/timezones.php`

```php
use App\Utils\Timezone;

// Get all IANA timezones
$allTimezones = Timezone::all(); // Returns array of IANA identifiers

// Get timezones as options (value => label with offset)
$options = Timezone::options();
// ['America/New_York' => 'Nueva York (UTC-05:00)', ...]

// Get timezones grouped by localized region
$grouped = Timezone::groupedByRegion();
// ['América' => [...], 'Europa' => [...], 'Asia' => [...]]
```

**Country Enum** (`app/Enums/CountryEnum.php`):
- All 249 countries with ISO 3166-1 alpha-2 codes
- Case names are country names (e.g., `UNITED_STATES`, `SPAIN`)
- Values are ISO 2 codes (e.g., `'US'`, `'ES'`)
- Fully localized via `lang/en/countries.php` and `lang/es/countries.php`

```php
use App\Enums\CountryEnum;

// Get all country codes
$codes = CountryEnum::values(); // ['AD', 'AE', 'AF', ...]

// Get countries as options (code => name)
$options = CountryEnum::options();
// ['US' => 'United States', 'ES' => 'Spain', ...]  (or Spanish equivalents)

// Access specific country
$usa = CountryEnum::UNITED_STATES; // Returns enum instance
$code = CountryEnum::UNITED_STATES->value; // 'US'
$name = CountryEnum::UNITED_STATES->label(); // 'United States' (localized)

// Get enum from code
$country = CountryEnum::fromCode('US'); // Returns CountryEnum::UNITED_STATES
```

**Currency Enum** (`app/Enums/CurrencyEnum.php`):
- All major currencies with ISO 4217 codes
- Case names are currency names (e.g., `UNITED_STATES_DOLLAR`, `EURO`)
- Values are ISO 3 codes (e.g., `'USD'`, `'EUR'`)
- Includes `symbol()` method for common currency symbols
- Fully localized via `lang/en/currencies.php` and `lang/es/currencies.php`

```php
use App\Enums\CurrencyEnum;

// Get all currency codes
$codes = CurrencyEnum::values(); // ['AED', 'AFN', 'ALL', ...]

// Get currencies as options (code => name)
$options = CurrencyEnum::options();
// ['USD' => 'United States Dollar', 'EUR' => 'Euro', ...]  (or Spanish equivalents)

// Access specific currency
$usd = CurrencyEnum::UNITED_STATES_DOLLAR; // Returns enum instance
$code = CurrencyEnum::UNITED_STATES_DOLLAR->value; // 'USD'
$name = CurrencyEnum::UNITED_STATES_DOLLAR->label(); // 'United States Dollar' (localized)
$symbol = CurrencyEnum::UNITED_STATES_DOLLAR->symbol(); // '$'

// Get enum from code
$currency = CurrencyEnum::fromCode('USD'); // Returns CurrencyEnum::UNITED_STATES_DOLLAR
```

**Translation Files**:
All timezone, country, and currency names are fully translated in both English and Spanish:
- `lang/en/timezones.php` & `lang/es/timezones.php` - 430+ IANA timezone translations
- `lang/en/countries.php` & `lang/es/countries.php` - 249 country translations
- `lang/en/currencies.php` & `lang/es/currencies.php` - 164+ currency translations
- `lang/en/continents.php` & `lang/es/continents.php` - Region names used by Timezone utility

These translations automatically use the current application locale.

**Usage in Forms**:
```blade
{{-- Timezone select --}}
<x-select
    label="{{ __('Timezone') }}"
    :options="App\Utils\Timezone::groupedByRegion()"
    wire:model="timezone"
/>

{{-- Country select --}}
<x-select
    label="{{ __('Country') }}"
    :options="App\Enums\CountryEnum::options()"
    wire:model="country_code"
/>

{{-- Currency select --}}
<x-select
    label="{{ __('Currency') }}"
    :options="App\Enums\CurrencyEnum::options()"
    wire:model="currency_code"
/>
```

### Frontend Stack
- **Livewire Components**: Place in `app/Livewire/` directory (currently empty but configured)
- **Blade Views**: Located in `resources/views/` with subdirectories:
  - `components/` - Blade components
  - `livewire/` - Livewire component views
- **Assets**:
  - CSS: `resources/css/app.css` - **Main theming file** (TailwindCSS + DaisyUI custom theme)
  - JS: `resources/js/app.js` (includes flatpickr and tom-select)
- **UI Components**:
  - Use Mary UI components in Blade views (prefix: `<x-component-name>`)
  - Mary UI is a bridge that brings DaisyUI components to Laravel/Livewire
  - DaisyUI uses TailwindCSS utility classes under the hood
  - All TailwindCSS classes available throughout the app
  - Custom DaisyUI theme configured in `resources/css/app.css`
- **Icons**:
  - Google Material Design Icons via `postare/blade-mdi`
  - Use in Mary components: `<x-icon name="mdi.icon-name" />`
  - Browse icons: https://pictogrammers.com/library/mdi/

### Permissions
- Configured via `config/permission.php`
- Uses Spatie Laravel Permission package
- Models are in `app/Models/Permission.php` and `app/Models/Role.php`

### Database
- **Migrations**: Located in `database/migrations/`
- **Key Tables**:
  - Users table with UUID primary key
  - Permission tables (roles, permissions, model_has_roles, etc.)
  - Cache, jobs, and standard Laravel tables

### Routing
- **Web Routes**: `routes/web.php` - all routes wrapped in localization group
- **Console Routes**: `routes/console.php`
- **Health Check**: `/up` endpoint configured

## Key Configuration Files

- `bootstrap/app.php` - Application bootstrap with localization middleware aliases
- `app/Providers/AppServiceProvider.php` - Gate::before for super_admin bypass
- `app/Enums/RoleEnum.php` - Core roles definition
- `app/Enums/PermissionEnum.php` - All permissions definition
- `app/Enums/CountryEnum.php` - All countries with ISO 2 codes
- `app/Enums/CurrencyEnum.php` - All currencies with ISO 3 codes
- `app/Utils/Timezone.php` - Timezone utility with IANA identifiers
- `app/Traits/HasCreator.php` - Automatic creator tracking trait
- `config/laravellocalization.php` - Extensive locale configuration
- `config/permission.php` - Permission system configuration
- `config/livewire-powergrid.php` - PowerGrid settings
- `lang/en/` & `lang/es/` - Translation files (countries, currencies, timezones, continents)
- `vite.config.js` - Vite configuration with TailwindCSS plugin
- `resources/css/app.css` - Main theming file (TailwindCSS + DaisyUI theme)

## Working with Livewire

**Documentation**: https://livewire.laravel.com/docs

When creating Livewire components:
1. Generate with: `php artisan make:livewire ComponentName`
2. Component class goes in `app/Livewire/`
3. View goes in `resources/views/livewire/`
4. Use Mary UI components for consistent UI (https://mary-ui.com/docs/installation)
5. For data tables, use PowerGrid: `php artisan powergrid:create` (https://livewire-powergrid.com/)

**Mary UI Components** (DaisyUI bridge for Livewire):
- Button: `<x-button>`, Card: `<x-card>`, Modal: `<x-modal>`, etc.
- Form components: `<x-input>`, `<x-select>`, `<x-textarea>`, etc.
- Full component list: https://mary-ui.com/docs/components/alert
- Mary UI automatically handles Livewire wire:model and validation

**PowerGrid Components**:
- Use for data tables with sorting, filtering, and pagination
- Supports bulk actions, exports, and custom actions
- Integrates with Mary UI styling

## Working with Translations

**Documentation**:
- Laravel Localization: https://github.com/mcamara/laravel-localization
- Laravel Lang: https://laravel-lang.com/introduction.html
- Translation Exporter: https://github.com/kkomelin/laravel-translatable-string-exporter

**Setup**:
1. Translation files are managed by `laravel-lang` packages
2. Configure supported locales in `config/laravellocalization.php` (uncomment to enable)
3. Currently enabled: English (en) and Spanish (es)

**Usage**:
- In views: `{{ __('key') }}` or `@lang('key')`
- With parameters: `{{ __('welcome.message', ['name' => $name]) }}`
- Get current locale: `LaravelLocalization::getCurrentLocale()`
- Get localized URL: `LaravelLocalization::getLocalizedURL('es', '/path')`
- All routes are automatically prefixed with locale (e.g., `/en/dashboard`, `/es/dashboard`)

**Extracting Translatable Strings**:
- Use `kkomelin/laravel-translatable-string-exporter` to extract `__()` and `@lang()` calls
- Run: `php artisan translatable:export` (check package docs for exact command)

**Adding New Locales**:
1. Uncomment the locale in `config/laravellocalization.php`
2. Install translations: `php artisan lang:add {locale}` (via laravel-lang)
3. Test locale switching works correctly

## Working with Roles & Permissions

**Documentation**: https://spatie.be/docs/laravel-permission/v6/introduction

### Architecture

**Enums** (Type-safe role and permission management):
- `app/Enums/RoleEnum.php` - Defines core roles: `SUPER_ADMIN`, `EMPLOYEE`
- `app/Enums/PermissionEnum.php` - Defines all permissions (User, Role, Permission management)

**Models & Config**:
- Models: `app/Models/Permission.php` and `app/Models/Role.php`
- Configuration: `config/permission.php`
- Database: `database/migrations/2025_10_26_035027_create_permission_tables.php`
- Seeder: `database/seeders/RoleAndPermissionSeeder.php`
- User model has `HasRoles` trait in `app/Models/User.php`

**Super Admin Bypass**:
- `app/Providers/AppServiceProvider.php` contains `Gate::before()` check
- Super Admin automatically passes ALL permission checks without needing permissions assigned
- Based on Spatie's recommendation: https://spatie.be/docs/laravel-permission/v6/basic-usage/super-admin

### Using Role & Permission Enums

**Get all roles or permissions**:
```php
use App\Enums\RoleEnum;
use App\Enums\PermissionEnum;

// Get all role values as array
$roles = RoleEnum::values(); // ['super_admin', 'employee']

// Get all permissions as array
$permissions = PermissionEnum::values(); // ['view_users', 'create_users', ...]

// Get options for dropdowns (value => label)
$roleOptions = RoleEnum::options(); // ['super_admin' => 'Super Admin', ...]
$permissionOptions = PermissionEnum::options();

// Get permissions grouped by category
$grouped = PermissionEnum::groupedByCategory();
// ['User Management' => [...], 'Role Management' => [...], ...]
```

**Assigning Roles & Permissions**:
```php
use App\Enums\RoleEnum;
use App\Enums\PermissionEnum;

// Assign role using enum
$user->assignRole(RoleEnum::EMPLOYEE->value);

// Give permission using enum
$user->givePermissionTo(PermissionEnum::VIEW_USERS->value);

// Assign multiple permissions to a role
$role->syncPermissions(PermissionEnum::forEmployee());
```

**Checking Permissions**:
```php
use App\Enums\RoleEnum;
use App\Enums\PermissionEnum;

// Check role
$user->hasRole(RoleEnum::SUPER_ADMIN->value);

// Check permission
$user->hasPermissionTo(PermissionEnum::EDIT_USERS->value);
$user->can(PermissionEnum::EDIT_USERS->value);

// Using enum helper
if (RoleEnum::SUPER_ADMIN->isSuperAdmin()) {
    // Do something
}
```

**In Blade/Livewire**:
```blade
@use(App\Enums\RoleEnum)
@use(App\Enums\PermissionEnum)

@can(PermissionEnum::EDIT_USERS->value)
    <!-- Content visible to users with edit_users permission -->
@endcan

@role(RoleEnum::SUPER_ADMIN->value)
    <!-- Content visible only to super admins -->
@endrole

@role(RoleEnum::EMPLOYEE->value)
    <!-- Content visible to employees -->
@endrole
```

**Middleware**:
```php
use App\Enums\RoleEnum;
use App\Enums\PermissionEnum;

Route::middleware(['role:' . RoleEnum::SUPER_ADMIN->value])->group(function () {
    // Routes for super admin only
});

Route::middleware(['permission:' . PermissionEnum::EDIT_USERS->value])->group(function () {
    // Routes requiring edit_users permission
});
```

### Dynamic Roles

- Core roles (`super_admin`, `employee`) are defined in `RoleEnum`
- Super admin can create additional roles dynamically through the application
- Dynamic roles work with the existing permission system
- Only core roles should be referenced in code; dynamic roles are managed via UI

### Seeding Roles & Permissions

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

This creates:
- All permissions from `PermissionEnum`
- Super Admin role (no permissions assigned - bypasses all checks)
- Employee role with default permissions (view_users)

### Adding New Permissions

1. Add new case to `PermissionEnum` in `app/Enums/PermissionEnum.php`
2. Add label and description in the `label()` and `description()` methods
3. Add category in the `category()` method
4. Run seeder: `php artisan db:seed --class=RoleAndPermissionSeeder`
5. Clear permission cache: `php artisan permission:cache-reset`

## UI Styling with TailwindCSS & DaisyUI

**Stack Overview**:
- TailwindCSS provides utility classes (https://tailwindcss.com/docs)
- DaisyUI adds component classes on top of Tailwind (https://daisyui.com/docs/intro/)
- Mary UI provides Livewire components that use DaisyUI styling (https://mary-ui.com/)

**Styling Approach**:
1. Use Mary UI components for interactive elements (forms, modals, tables)
2. Use DaisyUI classes for quick styling: `btn`, `card`, `badge`, etc.
3. Use TailwindCSS utilities for custom styling: `flex`, `mt-4`, `text-blue-500`, etc.
4. Mix all three approaches as needed

**Theme Configuration** (`resources/css/app.css`):
- **Main CSS Entry Point**: `resources/css/app.css` is where all theming happens
- **TailwindCSS**: Imported via `@import 'tailwindcss'`
- **Custom DaisyUI Theme**: Lines 23-56 define a custom "light" theme with:
  - Custom color palette (primary: green, secondary: pink, etc.)
  - Base colors for dark mode appearance (oklch color space)
  - Custom border radius and sizing for form elements
- **Content Sources**: `@source` directives tell TailwindCSS where to scan for classes:
  - Blade views, Livewire components, Mary UI components, PowerGrid components
- **Custom Font**: `@theme` block sets "Roboto" and "Instrument Sans" as default fonts
- **Additional Libraries**: Includes flatpickr (date picker) and tom-select (select dropdown) styles

**Customizing the Theme**:
1. Edit `resources/css/app.css` to change DaisyUI theme colors
2. Modify CSS variables like `--color-primary`, `--color-base-100`, etc.
3. Use TailwindCSS classes anywhere in your Blade/Livewire views
4. Use DaisyUI semantic color classes: `bg-primary`, `text-secondary`, `btn-accent`
5. After changes, rebuild: `npm run build` or restart `npm run dev`

**Available TailwindCSS Classes**:
- All standard TailwindCSS utilities work: spacing, flexbox, grid, typography, etc.
- DaisyUI adds semantic classes: `btn`, `card`, `modal`, `badge`, `alert`, etc.
- Custom `@apply` directives can be added to `resources/css/app.css` for reusable styles
- Mary UI components automatically use DaisyUI classes and adapt to your custom theme

## Common Patterns

**Creating a CRUD with Livewire + Mary UI**:
1. Generate Livewire component: `php artisan make:livewire ResourceManager`
2. Use Mary UI form components in the view
3. For listing: use PowerGrid (`php artisan powergrid:create`)
4. Add permissions checks with `@can` directives
5. Ensure all strings are translatable with `__()` helper

**Typical Component Structure**:
```php
// app/Livewire/ResourceManager.php
use App\Enums\PermissionEnum;

class ResourceManager extends Component
{
    public $form; // Use Livewire's form object

    public function save()
    {
        // Check permission
        $this->authorize(PermissionEnum::EDIT_USERS->value);

        $this->validate();
        // Save logic
    }

    public function render()
    {
        return view('livewire.resource-manager');
    }
}
```

**Blade View with Mary UI**:
```blade
@use(App\Enums\PermissionEnum)

<x-card title="{{ __('resources.title') }}">
    <x-form wire:submit="save">
        <x-input label="{{ __('Name') }}" wire:model="form.name" />
        <x-slot:actions>
            @can(PermissionEnum::EDIT_USERS->value)
                <x-button type="submit" class="btn-primary">
                    {{ __('Save') }}
                </x-button>
            @endcan
        </x-slot:actions>
    </x-form>
</x-card>
```

## Custom UI Components

The application includes custom Livewire form components located in `app/Livewire/Ui/Forms/`.

### PhoneNumberInput Component

A comprehensive phone number input component with country selection, flags, and validation using Google's libphonenumber library.

**Location**: `app/Livewire/Ui/Forms/PhoneNumberInput.php`

**Features**:
- Country selection with searchable dropdown
- Visual country flags (via `country-flag-icons` package)
- Automatic dial code display
- Real-time phone number validation using libphonenumber
- Stores numbers in E.164 format (+15551234567)
- Shows formatted international number preview
- Auto-selects authenticated user's country
- Built with Alpine.js and DaisyUI components

**Dependencies**:
- PHP: `giggsey/libphonenumber-for-php` (installed)
- NPM: `country-flag-icons` (installed)

**Basic Usage**:
```blade
<livewire:ui.forms.phone-number-input
    wire:model="phoneNumber"
    label="Phone Number"
/>
```

**With All Options**:
```blade
<livewire:ui.forms.phone-number-input
    wire:model="phoneNumber"
    label="Contact Number"
    hint="We'll use this to contact you"
    placeholder="Enter your phone number"
    :required="true"
    :disabled="false"
/>
```

**In Livewire Component**:
```php
class UserForm extends Component
{
    public string $phoneNumber = '';

    public function save()
    {
        $validated = $this->validate([
            'phoneNumber' => 'required|string',
        ]);

        // $validated['phoneNumber'] is in E.164 format: +15551234567
        // Ready to save to database
    }
}
```

**Props**:
- `wire:model` (required) - The phone number in E.164 format
- `label` (optional) - Label text for the input
- `hint` (optional) - Helper text shown below the input
- `placeholder` (optional) - Placeholder text
- `required` (optional) - Whether the field is required (default: false)
- `disabled` (optional) - Whether the input is disabled (default: false)

**Storage Format**:
Phone numbers are stored in E.164 format (international standard):
- Format: `+[country code][subscriber number]`
- Example: `+15551234567` (US), `+34912345678` (Spain)
- Database field: `$table->string('phone_number', 20)->nullable();`

**Example File**: See `resources/views/livewire/ui/forms/phone-number-input-example.blade.php` for complete usage examples.

## Testing Strategy

- Uses Pest PHP framework
- `tests/Pest.php` contains global test configuration
- Feature tests extend `Tests\TestCase`
- Database uses SQLite in-memory for tests
- RefreshDatabase trait is commented out by default - uncomment if needed

## Quick Reference

**Generate Components**:
- Livewire: `php artisan make:livewire ComponentName`
- PowerGrid: `php artisan powergrid:create TableName`
- Model: `php artisan make:model ModelName -mfs` (with migration, factory, seeder)

**Roles & Permissions**:
- Seed roles/permissions: `php artisan db:seed --class=RoleAndPermissionSeeder`
- Clear permission cache: `php artisan permission:cache-reset`
- Use enums: `RoleEnum::SUPER_ADMIN->value`, `PermissionEnum::EDIT_USERS->value`

**Data Management (Timezones, Countries, Currencies)**:
- Timezone options: `Timezone::options()` or `Timezone::groupedByRegion()`
- Country options: `CountryEnum::options()`
- Currency options: `CurrencyEnum::options()`
- All data is fully localized in English and Spanish
- Translation files: `lang/en/timezones.php`, `lang/en/countries.php`, `lang/en/currencies.php` (and `es/`)

**Check Documentation**:
- Laravel: https://laravel.com/docs/12.x/
- Livewire: https://livewire.laravel.com/docs
- Mary UI: https://mary-ui.com/docs/installation
- PowerGrid: https://livewire-powergrid.com/
- Spatie Permissions: https://spatie.be/docs/laravel-permission/v6/introduction
- DaisyUI: https://daisyui.com/docs/intro/
- Icons: https://pictogrammers.com/library/mdi/

**Common Issues**:
- If Mary UI components don't render: clear views (`php artisan view:clear`)
- If icons don't show: ensure `postare/blade-mdi` is installed and cache is cleared
- If translations don't work: check locale is in `config/laravellocalization.php`
- If permissions don't work:
  - Ensure role/permission tables are migrated and seeded
  - Ensure User model has `HasRoles` trait
  - Clear permission cache: `php artisan permission:cache-reset`
  - Check that `Gate::before()` is in `AppServiceProvider.php` for super_admin bypass
- If CSS changes don't appear: restart Vite dev server (`npm run dev`) or rebuild (`npm run build`)
- If TailwindCSS classes aren't working: ensure files are in `@source` paths in `resources/css/app.css`

**Adding Custom Styles**:
- Add custom CSS directly to `resources/css/app.css` (after the DaisyUI theme section)
- Use `@apply` directive to combine TailwindCSS classes into reusable classes
- Example: `.my-btn { @apply btn btn-primary rounded-lg shadow-md; }`
- Can also add custom CSS in Blade components using `<style>` tags (scoped per component)
