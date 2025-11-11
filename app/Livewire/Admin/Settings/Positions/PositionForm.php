<?php

namespace App\Livewire\Admin\Settings\Positions;

use App\Enums\PermissionEnum;
use App\Models\Position;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class PositionForm extends Component
{
    use Toast;

    public bool $showDrawer = false;
    public ?string $positionId = null;
    public string $name = '';

    public bool $isEditMode = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    #[On('create-position')]
    public function create(): void
    {
        if (!auth()->user()->can(PermissionEnum::CREATE_POSITIONS->value)) {
            $this->error(__('You do not have permission to create positions.'));
            return;
        }

        $this->reset(['name', 'positionId']);
        $this->isEditMode = false;
        $this->showDrawer = true;
    }

    #[On('edit-position')]
    public function edit(string $positionId): void
    {
        if (!auth()->user()->can(PermissionEnum::EDIT_POSITIONS->value)) {
            $this->error(__('You do not have permission to edit positions.'));
            return;
        }

        $position = Position::find($positionId);

        if (!$position) {
            $this->error(__('Position not found.'));
            return;
        }

        $this->positionId = $position->id;
        $this->name = $position->name;
        $this->isEditMode = true;
        $this->showDrawer = true;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function saveAndAddAnother(): void
    {
        if ($this->isEditMode) {
            return; // This action is only for create mode
        }

        $this->authorize(PermissionEnum::CREATE_POSITIONS->value);

        $validated = $this->validate();

        Position::create($validated);

        $this->success(__('Position created successfully.'));
        $this->reset(['name', 'positionId']);
        $this->resetValidation();
        $this->dispatch('pg:eventRefresh-positions-table');

        // Keep drawer open for another entry
    }

    protected function store(): void
    {
        $this->authorize(PermissionEnum::CREATE_POSITIONS->value);

        $validated = $this->validate();

        Position::create($validated);

        $this->success(__('Position created successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-positions-table');
    }

    protected function update(): void
    {
        $this->authorize(PermissionEnum::EDIT_POSITIONS->value);

        $validated = $this->validate();

        $position = Position::find($this->positionId);

        if (!$position) {
            $this->error(__('Position not found.'));
            return;
        }

        $position->update($validated);

        $this->success(__('Position updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-positions-table');
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->reset(['name', 'positionId', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.settings.positions.position-form');
    }
}
