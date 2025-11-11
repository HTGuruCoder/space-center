# Create CRUD Module

Create a complete CRUD module with PowerGrid table, form component, and all necessary files.

## Task

You will create a full-featured CRUD module including:

1. **Model** with migrations, factory, seeder
2. **PowerGrid Table Component** with all features
3. **Form Component** (Livewire) with validation
4. **Index Component** with table and form integration
5. **Blade views** (index, form, table actions, custom columns if needed)
6. **Routes** and **Permissions**
7. **Translations** (if needed)

## Information Needed

Ask the user for:
1. **Resource name** (singular, e.g., "Employee", "Product")
2. **Namespace/Module** (e.g., "Admin\Settings" or "Admin\Inventory")
3. **Database fields**:
   - Field name
   - Type (string, text, integer, boolean, date, etc.)
   - Validation rules
   - Required/optional
   - Default value (if any)
4. **Relationships**:
   - belongs_to, has_many, etc.
   - Foreign keys
5. **Special features**:
   - Soft deletes? (yes by default)
   - Track creator? (yes by default with HasCreator trait)
   - File uploads?
   - Custom validation logic?
6. **Display settings**:
   - Which fields to show in table
   - Which fields are searchable/sortable
   - Which fields in form
   - Form layout (single column, two columns, sections)

## Steps to Execute

### 1. Create Model with Migration

```bash
php artisan make:model {Model} -mfs
```

**Update migration**:
```php
Schema::create('{table}', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Add fields based on user input
    $table->string('name');
    // ...

    // Standard fields if using HasCreator
    $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
    $table->softDeletes(); // If using soft deletes
});
```

**Update Model**:
```php
<?php

namespace App\Models;

use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {Model} extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasCreator;

    protected $fillable = [
        // Add fillable fields based on user input
        'name',
        'created_by',
    ];

    protected $casts = [
        // Add casts based on field types
    ];

    // Add relationships based on user input
}
```

### 2. Run Migration

```bash
php artisan migrate
```

### 3. Create PowerGrid Table

Use `/create-powergrid-table` command or create manually following the same pattern.

### 4. Create Form Component

```bash
php artisan make:livewire {Namespace}\{Resource}Form
```

**Form Component** (`app/Livewire/{Namespace}/{Resource}Form.php`):
```php
<?php

namespace App\Livewire\{Namespace};

use App\Enums\PermissionEnum;
use App\Models\{Model};
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class {Resource}Form extends Component
{
    use Toast;

    public bool $showDrawer = false;
    public ?string ${singular}Id = null;
    public bool $isEditMode = false;

    // Form fields based on user input
    public string $name = '';
    // Add other fields...

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // Add validation rules based on user input
        ];
    }

    #[On('create-{resource}')]
    public function create(): void
    {
        if (!auth()->user()->can(PermissionEnum::CREATE_{RESOURCES}->value)) {
            $this->error(__('You do not have permission to create {resources}.'));
            return;
        }

        $this->reset(['name' /* add other fields */]);
        $this->isEditMode = false;
        $this->{$singular}Id = null;
        $this->showDrawer = true;
    }

    #[On('edit-{resource}')]
    public function edit(string ${singular}Id): void
    {
        if (!auth()->user()->can(PermissionEnum::EDIT_{RESOURCES}->value)) {
            $this->error(__('You do not have permission to edit {resources}.'));
            return;
        }

        ${singular} = {Model}::find(${singular}Id);

        if (!${singular}) {
            $this->error(__('{Resource} not found.'));
            return;
        }

        $this->{$singular}Id = ${singular}->id;
        $this->name = ${singular}->name;
        // Populate other fields...

        $this->isEditMode = true;
        $this->showDrawer = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    protected function store(): void
    {
        {Model}::create([
            'name' => $this->name,
            // Add other fields...
        ]);

        $this->success(__('{Resource} created successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-{table-name}');
    }

    protected function update(): void
    {
        ${singular} = {Model}::find($this->{$singular}Id);

        if (!${singular}) {
            $this->error(__('{Resource} not found.'));
            return;
        }

        ${singular}->update([
            'name' => $this->name,
            // Add other fields...
        ]);

        $this->success(__('{Resource} updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-{table-name}');
    }

    public function saveAndAddAnother(): void
    {
        $this->validate();
        $this->store();

        // Reset form but keep drawer open
        $this->reset(['name' /* add other fields */]);
        $this->{$singular}Id = null;
        $this->isEditMode = false;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view('{view-path}.{resource}-form');
    }
}
```

**Form View** (`resources/views/{path}/{resource}-form.blade.php`):
```blade
<x-drawer
    wire:model="showDrawer"
    :title="$isEditMode ? __('Edit {Resource}') : __('Create {Resource}')"
    right
    class="w-full sm:w-96 lg:w-1/3 max-w-full"
    separator
    with-close-button
>
    <x-form wire:submit="save">
        <div class="space-y-4">
            {{-- Add form fields based on user input --}}
            <x-input
                label="{{ __('Name') }}"
                wire:model="name"
                icon="mdi.form-textbox"
                required
            />

            {{-- Add other fields... --}}
        </div>

        <x-slot:actions>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center w-full gap-3">
                <x-button
                    label="{{ __('Cancel') }}"
                    @click="$wire.closeDrawer()"
                    class="order-last sm:order-first"
                />

                <div class="flex flex-col sm:flex-row gap-2">
                    @if(!$isEditMode)
                        <x-button
                            wire:click="saveAndAddAnother"
                            spinner="saveAndAddAnother"
                            class="btn-secondary"
                        >
                            {{ __('Add & Add Another') }}
                        </x-button>
                    @endif

                    <x-button
                        :label="$isEditMode ? __('Update') : __('Add')"
                        type="submit"
                        spinner="save"
                        class="btn-primary"
                    />
                </div>
            </div>
        </x-slot:actions>
    </x-form>
</x-drawer>
```

### 5. Update Index Component

Add method to open form:
```php
public function create{Resource}()
{
    $this->dispatch('create-{resource}');
}
```

### 6. Update Index View

Add form component:
```blade
{{-- Form Drawer --}}
<livewire:{namespace}.{resource}-form />
```

### 7. Add Permissions to PermissionEnum

```php
// {Resource} Management
case VIEW_{RESOURCES} = 'view_{resources}';
case CREATE_{RESOURCES} = 'create_{resources}';
case EDIT_{RESOURCES} = 'edit_{resources}';
case DELETE_{RESOURCES} = 'delete_{resources}';
```

Add to methods:
```php
public function label(): string
{
    return match ($this) {
        // ...
        self::VIEW_{RESOURCES} => __('View {Resources}'),
        self::CREATE_{RESOURCES} => __('Create {Resources}'),
        self::EDIT_{RESOURCES} => __('Edit {Resources}'),
        self::DELETE_{RESOURCES} => __('Delete {Resources}'),
        // ...
    };
}

public function description(): string
{
    return match ($this) {
        // ...
        self::VIEW_{RESOURCES} => __('Can view {resources} list and details'),
        self::CREATE_{RESOURCES} => __('Can create new {resources}'),
        self::EDIT_{RESOURCES} => __('Can edit existing {resources}'),
        self::DELETE_{RESOURCES} => __('Can delete {resources}'),
        // ...
    };
}

public function category(): string
{
    return match ($this) {
        // ...
        self::VIEW_{RESOURCES},
        self::CREATE_{RESOURCES},
        self::EDIT_{RESOURCES},
        self::DELETE_{RESOURCES} => __('{Resource} Management'),
        // ...
    };
}
```

### 8. Seed Permissions

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

### 9. Add Route

```php
Route::middleware(['role:' . RoleEnum::SUPER_ADMIN->value . '|' . RoleEnum::EMPLOYEE->value])
    ->prefix('settings')
    ->name('admins.settings.')
    ->group(function () {
        Route::get('/{resources}', \App\Livewire\{Namespace}\Index::class)->name('{resources}');
    });
```

## Testing Checklist

After creation, test:

### Table
- [ ] List displays correctly
- [ ] Pagination works
- [ ] Sorting works (all sortable columns)
- [ ] Filters work (text, date, relations)
- [ ] Search works
- [ ] Export works (XLSX, CSV)
- [ ] Bulk delete works
- [ ] Single delete works
- [ ] Delete validation works (if custom logic)
- [ ] Responsive on mobile

### Form
- [ ] Create works
- [ ] Edit works (pre-populates correctly)
- [ ] Validation works (all fields)
- [ ] "Add & Add Another" works
- [ ] Cancel resets form
- [ ] Success messages show
- [ ] Error messages show
- [ ] Permissions checked (create/edit)
- [ ] Responsive on mobile

### Integration
- [ ] Table refreshes after create
- [ ] Table refreshes after edit
- [ ] Table refreshes after delete
- [ ] Events dispatch correctly
- [ ] Permissions enforced everywhere

### Database
- [ ] Migration runs successfully
- [ ] Model relationships work
- [ ] HasCreator tracks creator correctly
- [ ] Soft deletes work
- [ ] UUIDs generated correctly

## Optional Enhancements

Ask user if they want:
1. **Search across relationships** (e.g., search by creator name)
2. **Custom filters** (e.g., status dropdown, date range)
3. **Bulk actions** (beyond delete, e.g., bulk status change)
4. **File uploads** with Livewire file handling
5. **Rich text editor** for description fields
6. **Multi-select** relationships
7. **Inline editing** in table
8. **Row actions** (beyond edit/delete)
9. **Export customization** (custom columns, formatting)
10. **Import from CSV/Excel**

## Best Practices Applied

✅ Extends `BasePowerGridComponent`
✅ Uses `HasBulkDelete` trait
✅ Uses `HasDeleteModal` trait
✅ Uses `HasCreator` trait on model
✅ Uses `PowerGridHelper` for common patterns
✅ Responsive design for mobile
✅ Permission checks everywhere
✅ Proper validation
✅ Toast notifications
✅ Proper event handling (Livewire 3 style)
✅ Translations with `__()`
✅ UUIDs as primary keys
✅ Soft deletes enabled
✅ Export functionality
✅ Organized file structure
