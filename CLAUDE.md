# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Livewire, multi-language support, and role-based permissions. The project uses:
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire 3, TailwindCSS 4, DaisyUI, Mary UI components
- **Testing**: Pest PHP
- **Build Tool**: Vite with Laravel plugin
- **Key Packages**:
  - `spatie/laravel-permission` - Role and permission management
  - `mcamara/laravel-localization` - Multi-language routing and localization
  - `livewire/livewire` - Full-stack framework for Laravel
  - `robsontenorio/mary` - Livewire component library
  - `power-components/livewire-powergrid` - DataTable component
  - `blade-ui-kit/blade-icons` + `postare/blade-mdi` - Material Design Icons

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
- **User Model**: Located in `app/Models/User.php`, uses HasFactory, Notifiable, and HasUuids traits
- **Permission System**: Custom `Permission` and `Role` models extend Spatie's permission package

### Localization
- **Route Localization**: All web routes are prefixed with locale (e.g., `/en/...`, `/es/...`)
- **Middleware Stack**: Routes use `localeCookieRedirect`, `localizationRedirect`, and `localeViewPath` middleware
- **Configuration**: `config/laravellocalization.php` defines supported locales
- **Current Setup**: English (en) and Spanish (es) are enabled by default
- **Locale Detection**: Uses browser locale detection via `codezero/browser-locale`

### Frontend Stack
- **Livewire Components**: Place in `app/Livewire/` directory (currently empty but configured)
- **Blade Views**: Located in `resources/views/` with subdirectories:
  - `components/` - Blade components
  - `livewire/` - Livewire component views
- **Assets**:
  - CSS: `resources/css/app.css` (TailwindCSS entry point)
  - JS: `resources/js/app.js` (includes flatpickr and tom-select)
- **UI Components**: Use Mary UI components in Blade views (prefix: `mary::`)
- **Icons**: Use Material Design Icons via Blade directives

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
- `config/laravellocalization.php` - Extensive locale configuration
- `config/permission.php` - Permission system configuration
- `config/livewire-powergrid.php` - PowerGrid settings
- `vite.config.js` - Vite configuration with TailwindCSS plugin

## Working with Livewire

When creating Livewire components:
1. Generate with: `php artisan make:livewire ComponentName`
2. Component class goes in `app/Livewire/`
3. View goes in `resources/views/livewire/`
4. Use Mary UI components for consistent UI

## Working with Translations

1. Translation files are managed by laravel-lang packages
2. Export strings: Use `kkomelin/laravel-translatable-string-exporter` package
3. Config in `config/laravellocalization.php` - uncomment locales to enable them
4. Access in views: `{{ __('key') }}` or `@lang('key')`
5. Get current locale: `LaravelLocalization::getCurrentLocale()`

## Testing Strategy

- Uses Pest PHP framework
- `tests/Pest.php` contains global test configuration
- Feature tests extend `Tests\TestCase`
- Database uses SQLite in-memory for tests
- RefreshDatabase trait is commented out by default - uncomment if needed
