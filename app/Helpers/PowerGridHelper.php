<?php

namespace App\Helpers;

use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use App\Helpers\DateHelper;

class PowerGridHelper
{
    /**
     * Get bulk delete button for header.
     */
    public static function getBulkDeleteButton(string $tableName, string $deletePermission): array
    {
        return [
            Button::add('bulk-actions')
                ->slot(view('components.powergrid.bulk-delete-button', [
                    'tableName' => $tableName,
                    'permission' => $deletePermission
                ])->render())
                ->class(''),
        ];
    }

    /**
     * Get standard date columns for PowerGrid tables.
     */
    public static function getDateColumns(): array
    {
        return [
            Column::make(__('Created At'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make(__('Created At'), 'created_at_export')
                ->hidden()
                ->visibleInExport(true),

            Column::make(__('Updated At'), 'updated_at_formatted', 'updated_at')
                ->sortable(),

            Column::make(__('Updated At'), 'updated_at_export')
                ->hidden()
                ->visibleInExport(true),
        ];
    }

    /**
     * Get standard date fields for PowerGrid tables.
     */
    public static function getDateFields(): array
    {
        return [
            'created_at' => fn($model) => $model->created_at,
            'created_at_formatted' => fn($model) => DateHelper::formatDateTime($model->created_at),
            'created_at_export' => fn($model) => $model->created_at?->toIso8601String() ?? '-',
            'updated_at' => fn($model) => $model->updated_at,
            'updated_at_formatted' => fn($model) => DateHelper::formatDateTime($model->updated_at),
            'updated_at_export' => fn($model) => $model->updated_at?->toIso8601String() ?? '-',
        ];
    }

    /**
     * Get standard creator fields for PowerGrid tables.
     */
    public static function getCreatorFields(): array
    {
        return [
            'creator_first_name' => fn($model) => $model->creator ? e($model->creator->first_name) : '-',
            'creator_last_name' => fn($model) => $model->creator ? e($model->creator->last_name) : '-',
        ];
    }

    /**
     * Get standard creator relation search configuration.
     */
    public static function getCreatorRelationSearch(): array
    {
        return [
            'creator' => ['first_name', 'last_name'],
        ];
    }

    /**
     * Get standard creator columns for PowerGrid tables.
     */
    public static function getCreatorColumns(): array
    {
        return [
            Column::make(__('Creator First Name'), 'creator_first_name', 'creator.first_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Creator Last Name'), 'creator_last_name', 'creator.last_name')
                ->sortable()
                ->searchable(),
        ];
    }

    /**
     * Get standard creator filters for PowerGrid tables.
     */
    public static function getCreatorFilters(): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Facades\Filter::inputText('creator_first_name')
                ->filterRelation('creator', 'first_name')
                ->placeholder(__('Creator first name')),

            \PowerComponents\LivewirePowerGrid\Facades\Filter::inputText('creator_last_name')
                ->filterRelation('creator', 'last_name')
                ->placeholder(__('Creator last name')),
        ];
    }

    /**
     * Get standard date filters for PowerGrid tables.
     *
     * @param string|null $table Table name to qualify the field (e.g., 'stores' becomes 'stores.created_at')
     */
    public static function getDateFilters(?string $table = null): array
    {
        $prefix = $table ? "{$table}." : '';

        return [
            \PowerComponents\LivewirePowerGrid\Facades\Filter::datetimepicker('created_at', "{$prefix}created_at"),
            \PowerComponents\LivewirePowerGrid\Facades\Filter::datetimepicker('updated_at', "{$prefix}updated_at"),
        ];
    }
}
