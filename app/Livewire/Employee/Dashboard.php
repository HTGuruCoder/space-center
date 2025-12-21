<?php

namespace App\Livewire\Employee;

use App\Helpers\DurationHelper;
use App\Models\EmployeeAbsence;
use App\Models\EmployeeBreak;
use App\Models\EmployeeWorkPeriod;
use App\Services\BreakService;
use App\Services\EmployeeService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use Toast;

    // KPI Properties
    public string $hoursWorkedThisWeek = '0min';
    public int $daysThisMonth = 0;
    public int $absencesThisMonth = 0;
    public int $subordinatesCount = 0;

    // Chart Properties
    public array $hoursPerDayChart = [];
    public array $absencesBreakdownChart = [];

    // Break status properties (used by the view)
    public bool $isOnBreak = false;
    public bool $canStartBreak = false;
    public bool $canEndBreak = false;
    public ?string $breakStartTime = null;
    public int $breakDurationMinutes = 0;
    public int $breakDurationSeconds = 0; // Pour le timer en temps réel
    public string $breakDurationFormatted = '0min';

    public function mount(BreakService $breakService)
    {
        // Ensure user has an employee record
        if (!auth()->user()->employee) {
            abort(403, __('You do not have an employee profile.'));
        }

        $this->loadKPIs();
        $this->loadCharts();
        $this->loadBreakStatus($breakService);
    }

    protected function loadBreakStatus(BreakService $breakService): void
    {
        $employee = auth()->user()->employee;
        $status = $breakService->getBreakStatus($employee);

        $this->isOnBreak = $status['is_on_break'];
        $this->canStartBreak = $status['can_start_break'];
        $this->canEndBreak = $status['can_end_break'];
        $this->breakStartTime = $status['break_start_time']?->format('H:i');
        $this->breakDurationMinutes = $status['break_duration_minutes'] ?? 0;
        $this->breakDurationFormatted = $status['break_duration_formatted'] ?? '0min';

        // Calculer la durée en secondes pour le timer en temps réel
        if ($status['is_on_break'] && $status['break_start_time']) {
            $this->breakDurationSeconds = $status['break_start_time']->diffInSeconds(now());
        } else {
            $this->breakDurationSeconds = 0;
        }
    }

    protected function loadKPIs(): void
    {
        $employee = auth()->user()->employee;

        // Hours Worked This Week (minus break time)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $workPeriods = EmployeeWorkPeriod::where('employee_id', $employee->id)
            ->whereBetween('clock_in_datetime', [$startOfWeek, $endOfWeek])
            ->whereNotNull('clock_in_datetime')
            ->whereNotNull('clock_out_datetime')
            ->get();

        $totalMinutes = 0;
        foreach ($workPeriods as $period) {
            $workMinutes = $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime);

            // Subtract break time for this work period
            $breakMinutes = EmployeeBreak::where('work_period_id', $period->id)
                ->completed()
                ->sum('duration_minutes') ?? 0;

            $totalMinutes += ($workMinutes - $breakMinutes);
        }
        $this->hoursWorkedThisWeek = DurationHelper::format(max(0, $totalMinutes));

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

        // Absences This Month (excluding breaks)
        $this->absencesThisMonth = EmployeeAbsence::where('employee_id', $employee->id)
            ->whereBetween('start_datetime', [$startOfMonth, $endOfMonth])
            ->whereHas('absenceType', function ($query) {
                $query->where('is_break', false);
            })
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
                $workMinutes = $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime);

                // Subtract break time
                $breakMinutes = EmployeeBreak::where('work_period_id', $period->id)
                    ->completed()
                    ->sum('duration_minutes') ?? 0;

                $totalMinutes += ($workMinutes - $breakMinutes);
            }

            $hours[] = round(max(0, $totalMinutes) / 60, 1);
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
            ->where('absence_types.is_break', false) // Exclude breaks
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
    public function clockIn(float $latitude, float $longitude, EmployeeService $employeeService, BreakService $breakService)
    {
        try {
            $employee = auth()->user()->employee;

            $employeeService->clockIn($employee, $latitude, $longitude);

            $this->success(__('Clocked in successfully!'));
            $this->dispatch('work-period-updated');
            $this->loadBreakStatus($breakService);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Clock out with geolocation.
     */
    public function clockOut(float $latitude, float $longitude, EmployeeService $employeeService, BreakService $breakService)
    {
        try {
            $employee = auth()->user()->employee;

            // Check if employee is on break - must end break first
            if ($breakService->isOnBreak($employee)) {
                $this->error(__('Please end your break before clocking out.'));
                return;
            }

            $employeeService->clockOut($employee, $latitude, $longitude);

            $this->success(__('Clocked out successfully!'));
            $this->dispatch('work-period-updated');
            $this->loadBreakStatus($breakService);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Start a break.
     */
    public function startBreak(BreakService $breakService, ?float $latitude = null, ?float $longitude = null)
    {
        try {
            $employee = auth()->user()->employee;
            $break = $breakService->startBreak($employee, $latitude, $longitude);

            $this->success(__('Break started at :time', [
                'time' => $break->start_datetime->format('H:i')
            ]));

            $this->dispatch('break-started');
            $this->loadBreakStatus($breakService);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * End the current break.
     */
    public function endBreak(BreakService $breakService, ?float $latitude = null, ?float $longitude = null)
    {
        try {
            $employee = auth()->user()->employee;
            $break = $breakService->endBreak($employee, $latitude, $longitude);

            $this->success(__('Break ended. Duration: :duration', [
                'duration' => $break->getFormattedDuration()
            ]));

            $this->dispatch('break-ended');
            $this->dispatch('work-period-updated');
            $this->loadBreakStatus($breakService);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Listen to work period updates and refresh the view.
     */
    #[On('work-period-updated')]
    #[On('break-started')]
    #[On('break-ended')]
    public function refreshWorkPeriod(BreakService $breakService)
    {
        $this->loadBreakStatus($breakService);
    }

    public function render(EmployeeService $employeeService, BreakService $breakService)
    {
        $employee = auth()->user()->employee;
        $activeWorkPeriod = $employeeService->getActiveWorkPeriod($employee);

        return view('livewire.employee.dashboard', [
            'activeWorkPeriod' => $activeWorkPeriod,
        ])
            ->layout('components.layouts.employee')
            ->title(__('Dashboard'));
    }
}