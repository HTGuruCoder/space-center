<?php

namespace App\Helpers;

use PowerComponents\LivewirePowerGrid\Column;
use App\Helpers\DateHelper;

class PowerGridHelper
{
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
