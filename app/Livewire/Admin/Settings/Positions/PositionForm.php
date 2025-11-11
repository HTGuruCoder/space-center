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
    public bool $isEditMode = false;

    // Form fields
    public string $name = '';

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

        $this->reset(['name']);
        $this->isEditMode = false;
        $this->positionId = null;
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
        $this->validate();

        if ($this->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    protected function store(): void
    {
        Position::create([
            'name' => $this->name,
        ]);

        $this->success(__('Position created successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-positions-table');
    }

    protected function update(): void
    {
        $position = Position::find($this->positionId);

        if (!$position) {
            $this->error(__('Position not found.'));
            return;
        }

        $position->update([
            'name' => $this->name,
        ]);

        $this->success(__('Position updated successfully.'));
        $this->closeDrawer();
        $this->dispatch('pg:eventRefresh-positions-table');
    }

    public function saveAndAddAnother(): void
    {
        $this->validate();
        $this->store();

        // Reset form but keep drawer open
        $this->reset(['name']);
        $this->positionId = null;
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
        return view('livewire.admin.settings.positions.position-form');
    }
}
