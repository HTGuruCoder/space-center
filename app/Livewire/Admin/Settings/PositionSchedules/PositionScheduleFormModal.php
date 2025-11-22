<?php

namespace App\Livewire\Admin\Settings\PositionSchedules;

use App\Enums\DayOfWeekEnum;
use App\Enums\PermissionEnum;
use App\Livewire\Forms\Admin\Settings\PositionScheduleForm;
use App\Models\Position;
use App\Models\PositionSchedule;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class PositionScheduleFormModal extends Component
{
    use Toast;

    public bool $show = false;

    public PositionScheduleForm $form;

    public bool $isEditMode = false;

    public $selectedTab = 'monday';

    #[On('create-position-schedule')]
    public function openCreate(): void
    {
        $this->authorize(PermissionEnum::CREATE_POSITION_SCHEDULES->value);

        $this->form->resetForm();
        $this->isEditMode = false;
        $this->show = true;
    }

    #[On('edit-position-schedule')]
    public function openEdit(string $positionId): void
    {
        $this->authorize(PermissionEnum::EDIT_POSITION_SCHEDULES->value);

        $position = Position::findOrFail($positionId);
        $this->form->setPosition($position);
        $this->isEditMode = true;
        $this->show = true;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->authorize(PermissionEnum::EDIT_POSITION_SCHEDULES->value);
        } else {
            $this->authorize(PermissionEnum::CREATE_POSITION_SCHEDULES->value);
        }

        try {
            $this->form->validateWithOverlapCheck();

            DB::transaction(function () {
                // Delete all existing schedules for this position if editing
                if ($this->isEditMode) {
                    PositionSchedule::where('position_id', $this->form->positionId)->delete();
                }

                // Create new schedules from form data
                foreach ($this->form->events as $day => $dayEvents) {
                    foreach ($dayEvents as $event) {
                        PositionSchedule::create([
                            'position_id' => $this->form->positionId,
                            'week_day' => $day,
                            'title' => $event['title'],
                            'description' => $event['description'] ?? null,
                            'start_time' => $event['start_time'],
                            'end_time' => $event['end_time'],
                        ]);
                    }
                }
            });

            $message = $this->isEditMode
                ? __('Position schedule updated successfully.')
                : __('Position schedule created successfully.');

            $this->success($message);
            $this->dispatch('pg:eventRefresh-position-schedules-table');
            $this->close();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function addEventForDay(string $day): void
    {
        $this->form->addEvent($day);
    }

    public function removeEventForDay(string $day, int $index): void
    {
        $this->form->removeEvent($day, $index);
    }

    public function close(): void
    {
        $this->show = false;
        $this->form->resetForm();
        $this->isEditMode = false;
    }

    public function render()
    {
        return view('livewire.admin.settings.position-schedules.position-schedule-form-modal', [
            'positions' => cache()->remember(
                'positions_select_options',
                now()->addMinutes(10),
                fn() => Position::orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
                    ->toArray()
            ),
            'days' => DayOfWeekEnum::cases(),
        ]);
    }
}
