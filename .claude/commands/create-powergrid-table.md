# Create PowerGrid Table

Create a new PowerGrid table component with all best practices and reusable components.

## Task

You will create a complete PowerGrid table setup with the following components:

1. **PowerGrid Table Component** extending `BasePowerGridComponent`
2. **Using appropriate traits** (`HasBulkDelete`)
3. **Index Component** with `HasDeleteModal` trait
4. **Blade views** (index, actions partial)
5. **Route** in web.php
6. **Permissions** in PermissionEnum (if needed)

## Information Needed

Before starting, ask the user for:
1. **Resource name** (e.g., "Employee", "Product", "Invoice")
2. **Table fields** to display (e.g., name, email, status, etc.)
3. **Searchable/sortable fields**
4. **Relationships** (if any, e.g., belongs to creator, has many items)
5. **Special validation** for deletion (if any)
6. **Namespace/path** (e.g., `Admin\Settings\Employees` or just `Employees`)

## Steps to Execute

### 1. Generate PowerGrid Component

```bash
php artisan powergrid:create {NameTable}
```

Move it to the correct namespace if needed.

### 2. Update PowerGrid Table Component

Extend `BasePowerGridComponent` and use `HasBulkDelete` trait:

```php
<?php

namespace App\Livewire\{Namespace};

use App\Enums\PermissionEnum;
use App\Helpers\PowerGridHelper;
use App\Livewire\BasePowerGridComponent;
use App\Models\{Model};
use App\Traits\Livewire\HasBulkDelete;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class {Name}Table extends BasePowerGridComponent
{
    use HasBulkDelete;

    public string $tableName = '{table-name}';
    public string $sortField = '{table}.created_at';

    protected function getExportFileName(): string
    {
        return '{table}-export';
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_{RESOURCES}->value;
    }

    protected function getModelClass(): string
    {
        return {Model}::class;
    }

    public function header(): array
    {
        return [
            ...PowerGridHelper::getBulkDeleteButton(
                $this->tableName,
                $this->getDeletePermission()
            ),
        ];
    }

    public function datasource(): Builder
    {
        return {Model}::query()
            ->select('{table}.*')
            ->leftJoin('users as creator', '{table}.created_by', '=', 'creator.id')
            ->with('creator:id,first_name,last_name');
    }

    public function relationSearch(): array
    {
        return [
            ...PowerGridHelper::getCreatorRelationSearch(),
        ];
    }

    public function fields(): PowerGridFields
    {
        $fields = PowerGrid::fields()
            ->add('id')
            ->add('actions', fn({Model} $model) => view('{view-path}.actions', [
                '{singular}Id' => $model->id
            ])->render())
            // Add custom fields here based on user input
            ;

        // Add creator fields
        foreach (PowerGridHelper::getCreatorFields() as $key => $callback) {
            $fields->add($key, $callback);
        }

        // Add date fields
        foreach (PowerGridHelper::getDateFields() as $key => $callback) {
            $fields->add($key, $callback);
        }

        return $fields;
    }

    public function columns(): array
    {
        return [
            Column::add()
                ->title(__('Actions'))
                ->field('actions')
                ->visibleInExport(false)
                ->bodyAttribute('class', 'w-16')
                ->headerAttribute('class', 'w-16'),

            // Add custom columns here based on user input

            ...PowerGridHelper::getCreatorColumns(),
            ...PowerGridHelper::getDateColumns(),
        ];
    }

    public function filters(): array
    {
        return [
            // Add custom filters here based on user input

            ...PowerGridHelper::getCreatorFilters(),
            ...PowerGridHelper::getDateFilters('{table}'),
        ];
    }
}
```

### 3. Create Index Component

```php
<?php

namespace App\Livewire\{Namespace};

use App\Enums\PermissionEnum;
use App\Models\{Model};
use App\Traits\Livewire\HasDeleteModal;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use HasDeleteModal;

    #[On('delete-{resource}')]
    public function handleDelete(string ${singular}Id): void
    {
        $this->confirmDelete(${singular}Id);
    }

    protected function getDeletePermission(): string
    {
        return PermissionEnum::DELETE_{RESOURCES}->value;
    }

    protected function getModelClass(): string
    {
        return {Model}::class;
    }

    protected function getRefreshEvent(): string
    {
        return 'pg:eventRefresh-{table-name}';
    }

    protected function getDeleteSuccessMessage(): string
    {
        return __('{Resource} deleted successfully.');
    }

    // Add custom deletion validation if needed
    protected function canDelete($model): bool
    {
        // Add validation logic here
        return true;
    }

    public function render()
    {
        return view('{view-path}.index')
            ->layout('components.layouts.admin')
            ->title(__('{Resources}'));
    }
}
```

### 4. Create Blade Views

**Index view** (`resources/views/{path}/index.blade.php`):
```blade
@use(App\Enums\PermissionEnum)

<div>
    {{-- Header with Create Button --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold">{{ __('{Resources}') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Manage your {resources}') }}</p>
        </div>

        @can(PermissionEnum::CREATE_{RESOURCES}->value)
            <button wire:click="create{Resource}" class="btn btn-primary">
                <x-icon name="mdi.plus" class="w-5 h-5" />
                <span>{{ __('New {Resource}') }}</span>
            </button>
        @endcan
    </div>

    {{-- PowerGrid Table --}}
    <div class="bg-base-100 shadow-xl rounded-lg px-2 py-4">
        <livewire:{namespace}.{table-name} />
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-powergrid.delete-modal
        :title="__('Delete {Resource}')"
        :message="__('Are you sure you want to delete this {resource}? This action cannot be undone.')"
    />
</div>
```

**Actions partial** (`resources/views/{path}/{table-name}/actions.blade.php`):
```blade
@use(App\Enums\PermissionEnum)

<x-dropdown>
    <x-slot:trigger>
        <x-button icon="mdi.dots-vertical" class="btn-ghost btn-sm btn-circle" />
    </x-slot:trigger>

    @can(PermissionEnum::EDIT_{RESOURCES}->value)
        <x-menu-item
            title="{{ __('Edit') }}"
            icon="mdi.pencil"
            wire:click="$dispatch('edit-{resource}', { {singular}Id: '{{ ${singular}Id }}' })"
        />
    @endcan

    @can(PermissionEnum::DELETE_{RESOURCES}->value)
        <x-menu-item
            title="{{ __('Delete') }}"
            icon="mdi.delete"
            class="text-error"
            wire:click="$dispatch('delete-{resource}', { {singular}Id: '{{ ${singular}Id }}' })"
        />
    @endcan
</x-dropdown>
```

### 5. Add Route

Add to `routes/web.php`:
```php
Route::get('/{resources}', \App\Livewire\{Namespace}\Index::class)->name('{prefix}.{resources}');
```

### 6. Add Permissions (if needed)

Add to `app/Enums/PermissionEnum.php`:
```php
// {Resource} Management
case VIEW_{RESOURCES} = 'view_{resources}';
case CREATE_{RESOURCES} = 'create_{resources}';
case EDIT_{RESOURCES} = 'edit_{resources}';
case DELETE_{RESOURCES} = 'delete_{resources}';
```

Then in the enum methods, add labels, descriptions, and category.

## After Creation

1. Run `php artisan db:seed --class=RoleAndPermissionSeeder` to create new permissions
2. Test the table:
   - Pagination works
   - Sorting works
   - Filters work (especially dates with JOINs)
   - Bulk delete works
   - Single delete works with validation
   - Export works
3. Check responsive design on mobile

## Best Practices

- Use `leftJoin` for creator relationship to enable sorting
- Qualify column names in filters when using JOINs: `getDateFilters('table_name')`
- Always use `HasCreator` trait on models that track creator
- Add custom validation in `canDelete()` method
- Use spread operator for flexibility in columns/filters
- Keep views organized in subdirectories by feature
