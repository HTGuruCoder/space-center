<?php

namespace App\Livewire\Forms\Admin\Employees;

use App\Models\EmployeeAllowedLocation;
use Livewire\Form;

class AllowedLocationForm extends Form
{
    public ?string $locationId = null;
    public bool $isEditMode = false;

    public ?string $employee_id = null;
    public ?string $name = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?string $valid_from = null;
    public ?string $valid_until = null;

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ];
    }

    public function setLocation(EmployeeAllowedLocation $location): void
    {
        $this->isEditMode = true;
        $this->locationId = $location->id;
        $this->employee_id = $location->employee_id;
        $this->name = $location->name;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->valid_from = $location->valid_from;
        $this->valid_until = $location->valid_until;
    }

    public function resetForm(): void
    {
        $this->reset();
    }

    public function getData(): array
    {
        return [
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'valid_from' => $this->valid_from,
            'valid_until' => $this->valid_until,
        ];
    }
}
