<?php

namespace App\Livewire\Employee\Subordinates;

use Livewire\Component;

class OrganizationChart extends Component
{
    public function mount()
    {
        // Ensure user has an employee record
        if (!auth()->user()->employee) {
            abort(403, __('You do not have an employee profile.'));
        }
    }

    /**
     * Build organization tree recursively
     */
    private function buildOrgTree($employee, $depth = 0, $maxDepth = 3)
    {
        if ($depth >= $maxDepth) {
            return null;
        }

        $subordinates = $employee->subordinates()
            ->with(['user', 'position', 'store'])
            ->whereNull('ended_at')
            ->whereNull('stopped_at')
            ->orderBy('created_at')
            ->get();

        $node = [
            'id' => $employee->id,
            'name' => $employee->user->full_name,
            'position' => $employee->position->name ?? __('No Position'),
            'store' => $employee->store->name ?? __('No Store'),
            'avatar' => $employee->user->getProfilePictureUrl(),
            'initials' => $employee->user->initials ?? strtoupper(substr($employee->user->first_name, 0, 1) . substr($employee->user->last_name, 0, 1)),
            'is_current' => $employee->id === auth()->user()->employee->id,
            'children' => [],
        ];

        foreach ($subordinates as $subordinate) {
            $childNode = $this->buildOrgTree($subordinate, $depth + 1, $maxDepth);
            if ($childNode) {
                $node['children'][] = $childNode;
            }
        }

        return $node;
    }

    public function render()
    {
        // Load employee with all necessary relationships
        $employee = auth()->user()->employee->load(['user', 'position', 'store']);

        // Build organization tree starting from current employee
        $orgTree = $this->buildOrgTree($employee);

        return view('livewire.employee.subordinates.organization-chart', [
            'orgTree' => $orgTree,
            'hasSubordinates' => isset($orgTree['children']) && count($orgTree['children']) > 0,
        ])
            ->layout('components.layouts.employee')
            ->title(__('Organization Chart'));
    }
}
