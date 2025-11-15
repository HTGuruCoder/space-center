<?php

namespace App\Livewire\Admin\Employees\Profiles;

use App\Enums\PermissionEnum;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class StopContractModal extends Component
{
    use Toast;

    public bool $showModal = false;
    public ?string $userId = null;
    public ?string $stopped_at = null;
    public ?string $stop_reason = null;

    public function mount()
    {
        $this->stopped_at = now()->format('Y-m-d');
    }

    #[On('stop-employee-contract')]
    public function handleStopContract(string $userId): void
    {
        $this->authorize(PermissionEnum::EDIT_EMPLOYEES->value);

        $user = User::with('employee')->findOrFail($userId);

        if ($user->employee === null) {
            $this->error(__('No employee profile found.'));
            return;
        }

        if ($user->employee->stopped_at !== null) {
            $this->error(__('Employee contract is already stopped.'));
            return;
        }

        $this->userId = $userId;
        $this->stopped_at = now()->format('Y-m-d');
        $this->stop_reason = null;
        $this->showModal = true;
    }

    public function stopContract(): void
    {
        $this->authorize(PermissionEnum::EDIT_EMPLOYEES->value);

        $validated = $this->validate([
            'stopped_at' => 'required|date',
            'stop_reason' => 'required|string|max:1000',
        ]);

        $user = User::with('employee')->findOrFail($this->userId);

        if ($user->employee === null) {
            $this->error(__('No employee profile found.'));
            $this->closeModal();
            return;
        }

        $user->employee->update([
            'stopped_at' => $validated['stopped_at'],
            'stop_reason' => $validated['stop_reason'],
        ]);

        $this->success(__('Employee contract stopped successfully.'));
        $this->dispatch('pg:eventRefresh-employee-profiles-table');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->userId = null;
        $this->stopped_at = now()->format('Y-m-d');
        $this->stop_reason = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.employees.profiles.stop-contract-modal');
    }
}
