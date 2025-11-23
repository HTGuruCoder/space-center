<?php

namespace App\Livewire\Employee;

use App\Helpers\DurationHelper;
use App\Models\EmployeeAbsence;
use App\Models\EmployeeWorkPeriod;
use App\Services\EmployeeService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;

    public $showLunchBreakModal = false;

    // KPI Properties
    public string $hoursWorkedThisWeek = '0min';
    public int $daysThisMonth = 0;
    public int $absencesThisMonth = 0;
    public int $subordinatesCount = 0;

    // Chart Properties
    public array $hoursPerDayChart = [];
    public array $absencesBreakdownChart = [];

    public function mount()
    {
        // Ensure user has an employee record
        if (!auth()->user()->employee) {
            abort(403, __('You do not have an employee profile.'));
        }

        $this->loadKPIs();
        $this->loadCharts();
    }

    protected function loadKPIs(): void
    {
        $employee = auth()->user()->employee;

        // Hours Worked This Week
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $workPeriods = EmployeeWorkPeriod::where('employee_id', $employee->id)
            ->whereBetween('clock_in_datetime', [$startOfWeek, $endOfWeek])
            ->whereNotNull('clock_in_datetime')
            ->whereNotNull('clock_out_datetime')
            ->get();

        $totalMinutes = 0;
        foreach ($workPeriods as $period) {
            $totalMinutes += $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime);
        }
        $this->hoursWorkedThisWeek = DurationHelper::format($totalMinutes);

        // Days Present This Month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $this->daysThisMonth = EmployeeWorkPeriod::where('employee_id', $employee->id)
            ->whereBetween('clock_in_datetime', [$startOfMonth, $endOfMonth])
            ->whereNotNull('clock_in_datetime')
            ->whereNotNull('clock_out_datetime')
            ->selectRaw('DATE(clock_in_datetime) as work_date')
            ->distinct()
            ->count();

        // Absences This Month
        $this->absencesThisMonth = EmployeeAbsence::where('employee_id', $employee->id)
            ->whereBetween('start_datetime', [$startOfMonth, $endOfMonth])
            ->count();

        // Subordinates Count
        $this->subordinatesCount = $employee->subordinates()->count();
    }

    protected function loadCharts(): void
    {
        $this->loadHoursPerDayChart();
        $this->loadAbsencesBreakdownChart();
    }

    protected function loadHoursPerDayChart(): void
    {
        $employee = auth()->user()->employee;
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $days = [];
        $hours = [];

        // Generate labels for each day of the week
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $days[] = $date->translatedFormat('D'); // Mon, Tue, Wed, etc.

            // Calculate hours for this day
            $workPeriods = EmployeeWorkPeriod::where('employee_id', $employee->id)
                ->whereDate('clock_in_datetime', $date)
                ->whereNotNull('clock_in_datetime')
                ->whereNotNull('clock_out_datetime')
                ->get();

            $totalMinutes = 0;
            foreach ($workPeriods as $period) {
                $totalMinutes += $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime);
            }

            $hours[] = round($totalMinutes / 60, 1);
        }

        $this->hoursPerDayChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $days,
                'datasets' => [
                    [
                        'label' => __('Hours'),
                        'data' => $hours,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                        'borderColor' => 'rgba(34, 197, 94, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => null, // Will be set in JavaScript
                        ],
                    ],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => ['display' => true, 'text' => __('Hours')],
                        'ticks' => [
                            'callback' => null, // Will be set in JavaScript
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function loadAbsencesBreakdownChart(): void
    {
        $employee = auth()->user()->employee;
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $absencesData = EmployeeAbsence::where('employee_id', $employee->id)
            ->whereBetween('start_datetime', [$startOfMonth, $endOfMonth])
            ->join('absence_types', 'employee_absences.absence_type_id', '=', 'absence_types.id')
            ->select('absence_types.name', \DB::raw('COUNT(*) as count'))
            ->groupBy('absence_types.id', 'absence_types.name')
            ->get();

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(234, 179, 8, 0.8)',
        ];

        $this->absencesBreakdownChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $absencesData->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'data' => $absencesData->pluck('count')->toArray(),
                        'backgroundColor' => array_slice($colors, 0, $absencesData->count()),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
            ],
        ];
    }

    /**
     * Clock in with geolocation.
     */
    public function clockIn(float $latitude, float $longitude, EmployeeService $employeeService)
    {
        try {
            $employee = auth()->user()->employee;

            $employeeService->clockIn($employee, $latitude, $longitude);

            $this->success(__('Clocked in successfully!'));
            $this->dispatch('work-period-updated');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Clock out with geolocation.
     */
    public function clockOut(float $latitude, float $longitude, EmployeeService $employeeService)
    {
        try {
            $employee = auth()->user()->employee;

            $employeeService->clockOut($employee, $latitude, $longitude);

            $this->success(__('Clocked out successfully!'));
            $this->dispatch('work-period-updated');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Open lunch break modal.
     */
    public function requestLunchBreak(EmployeeService $employeeService)
    {
        $employee = auth()->user()->employee;
        $activeWorkPeriod = $employeeService->getActiveWorkPeriod($employee);

        $this->dispatch('show-lunch-break-modal', [
            'hasActiveWorkPeriod' => $activeWorkPeriod !== null,
            'clockInTime' => $activeWorkPeriod?->clock_in_datetime->format('H:i'),
        ]);
    }

    /**
     * Listen to work period updates and refresh the view.
     */
    #[On('work-period-updated')]
    public function refreshWorkPeriod()
    {
        // Component will re-render automatically
    }

    public function render(EmployeeService $employeeService)
    {
        $employee = auth()->user()->employee;
        $activeWorkPeriod = $employeeService->getActiveWorkPeriod($employee);

        // Check if lunch break is available
        $canTakeLunchBreak = $this->canTakeLunchBreak();

        return view('livewire.employee.dashboard', [
            'activeWorkPeriod' => $activeWorkPeriod,
            'canTakeLunchBreak' => $canTakeLunchBreak,
        ])
            ->layout('components.layouts.employee')
            ->title(__('Dashboard'));
    }

    /**
     * Check if employee can take lunch break today.
     */
    private function canTakeLunchBreak(): bool
    {
        $employee = auth()->user()->employee;

        // Find lunch break absence type
        $lunchType = \App\Models\AbsenceType::where('is_break', true)->first();

        if (!$lunchType || !$lunchType->max_per_day) {
            return true; // No limit configured
        }

        // Count today's lunch breaks
        $todayBreaksCount = \App\Models\EmployeeAbsence::where('employee_id', $employee->id)
            ->where('absence_type_id', $lunchType->id)
            ->whereDate('start_datetime', today())
            ->count();

        return $todayBreaksCount < $lunchType->max_per_day;
    }
}
