<?php

namespace App\Livewire;

use Mary\Traits\Toast;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

abstract class BasePowerGridComponent extends PowerGridComponent
{
    use WithExport, Toast;

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(perPage: 100, perPageValues: [10, 25, 50, 100, 250])
                ->showRecordCount(),

            PowerGrid::exportable(fileName: $this->getExportFileName())
                ->type('xlsx', 'csv')
                ->striped(),
        ];
    }

    /**
     * Get the filename for exports.
     */
    abstract protected function getExportFileName(): string;
}
