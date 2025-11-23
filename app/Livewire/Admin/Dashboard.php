<?php

namespace App\Livewire\Admin;

use App\Models\Employee;
use App\Models\EmployeeAbsence;
use App\Models\EmployeeWorkPeriod;
use App\Models\Store;
use App\Models\Position;
use App\Models\AbsenceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // KPI Properties
    public int $totalActiveEmployees = 0;
    public int $newEmployeesThisMonth = 0;
    public int $absencesToday = 0;
    public float $hoursWorkedThisWeek = 0;
    public int $employeesOnProbation = 0;
    public float $retentionRate = 0;

    // Chart Properties
    public array $hoursPerStoreChart = [];
    public array $employeesByPositionChart = [];
    public array $absencesTrendChart = [];
    public array $topAbsenceTypesChart = [];
    public array $employeesByTypeChart = [];
    public array $probationByStoreChart = [];

    public function mount(): void
    {
        $this->loadKPIs();
        $this->loadCharts();
    }

    protected function loadKPIs(): void
    {
        // Total Active Employees (not ended and not stopped)
        $this->totalActiveEmployees = Employee::whereNull('ended_at')
            ->whereNull('stopped_at')
            ->count();

        // New Employees This Month
        $this->newEmployeesThisMonth = Employee::whereYear('started_at', Carbon::now()->year)
            ->whereMonth('started_at', Carbon::now()->month)
            ->count();

        // Absences Today
        $today = Carbon::today();
        $this->absencesToday = EmployeeAbsence::whereDate('start_datetime', $today)->count();

        // Hours Worked This Week
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $workPeriods = EmployeeWorkPeriod::whereBetween('clock_in_datetime', [$startOfWeek, $endOfWeek])
            ->whereNotNull('clock_in_datetime')
            ->whereNotNull('clock_out_datetime')
            ->get();

        $totalMinutes = 0;
        foreach ($workPeriods as $period) {
            $totalMinutes += $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime);
        }
        $this->hoursWorkedThisWeek = round($totalMinutes / 60, 1);

        // Employees on Probation (probation_period > 0 and started within probation period)
        $this->employeesOnProbation = Employee::whereNull('ended_at')
            ->whereNull('stopped_at')
            ->where('probation_period', '>', 0)
            ->whereRaw('DATE_ADD(started_at, INTERVAL probation_period DAY) >= CURDATE()')
            ->count();

        // Retention Rate
        $totalCreated = Employee::count();
        $this->retentionRate = $totalCreated > 0
            ? round(($this->totalActiveEmployees / $totalCreated) * 100, 1)
            : 0;
    }

    protected function loadCharts(): void
    {
        $this->loadHoursPerStoreChart();
        $this->loadEmployeesByPositionChart();
        $this->loadAbsencesTrendChart();
        $this->loadTopAbsenceTypesChart();
        $this->loadEmployeesByTypeChart();
        $this->loadProbationByStoreChart();
    }

    protected function loadHoursPerStoreChart(): void
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $storesData = Store::select('stores.id', 'stores.name')
            ->leftJoin('employees', 'stores.id', '=', 'employees.store_id')
            ->leftJoin('employee_work_periods', 'employees.id', '=', 'employee_work_periods.employee_id')
            ->whereBetween('employee_work_periods.clock_in_datetime', [$startOfMonth, $endOfMonth])
            ->whereNotNull('employee_work_periods.clock_in_datetime')
            ->whereNotNull('employee_work_periods.clock_out_datetime')
            ->groupBy('stores.id', 'stores.name')
            ->get()
            ->map(function ($store) use ($startOfMonth, $endOfMonth) {
                $workPeriods = EmployeeWorkPeriod::whereHas('employee', function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                })
                ->whereBetween('clock_in_datetime', [$startOfMonth, $endOfMonth])
                ->whereNotNull('clock_in_datetime')
                ->whereNotNull('clock_out_datetime')
                ->get();

                $totalMinutes = 0;
                foreach ($workPeriods as $period) {
                    $totalMinutes += $period->clock_in_datetime->diffInMinutes($period->clock_out_datetime);
                }

                return [
                    'name' => $store->name,
                    'hours' => round($totalMinutes / 60, 1),
                ];
            });

        $this->hoursPerStoreChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $storesData->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => __('Hours Worked'),
                        'data' => $storesData->pluck('hours')->toArray(),
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
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => ['display' => true, 'text' => __('Hours')],
                    ],
                ],
            ],
        ];
    }

    protected function loadEmployeesByPositionChart(): void
    {
        $positionsData = Position::select('positions.name', DB::raw('COUNT(employees.id) as count'))
            ->leftJoin('employees', 'positions.id', '=', 'employees.position_id')
            ->whereNull('employees.ended_at')
            ->whereNull('employees.stopped_at')
            ->groupBy('positions.id', 'positions.name')
            ->having('count', '>', 0)
            ->get();

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(234, 179, 8, 0.8)',
        ];

        $this->employeesByPositionChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $positionsData->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'data' => $positionsData->pluck('count')->toArray(),
                        'backgroundColor' => array_slice($colors, 0, $positionsData->count()),
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

    protected function loadAbsencesTrendChart(): void
    {
        $months = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');

            $count = EmployeeAbsence::whereYear('start_datetime', $date->year)
                ->whereMonth('start_datetime', $date->month)
                ->count();

            $counts[] = $count;
        }

        $this->absencesTrendChart = [
            'type' => 'line',
            'data' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => __('Absences'),
                        'data' => $counts,
                        'borderColor' => 'rgba(239, 68, 68, 1)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ];
    }

    protected function loadTopAbsenceTypesChart(): void
    {
        $absenceTypes = AbsenceType::select('absence_types.name', DB::raw('COUNT(employee_absences.id) as count'))
            ->leftJoin('employee_absences', 'absence_types.id', '=', 'employee_absences.absence_type_id')
            ->groupBy('absence_types.id', 'absence_types.name')
            ->having('count', '>', 0)
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $this->topAbsenceTypesChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $absenceTypes->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => __('Count'),
                        'data' => $absenceTypes->pluck('count')->toArray(),
                        'backgroundColor' => 'rgba(251, 146, 60, 0.8)',
                        'borderColor' => 'rgba(251, 146, 60, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
            'options' => [
                'indexAxis' => 'y',
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ];
    }

    protected function loadEmployeesByTypeChart(): void
    {
        $types = Employee::select('type', DB::raw('COUNT(*) as count'))
            ->whereNull('ended_at')
            ->whereNull('stopped_at')
            ->groupBy('type')
            ->get();

        $this->employeesByTypeChart = [
            'type' => 'pie',
            'data' => [
                'labels' => $types->pluck('type')->toArray(),
                'datasets' => [
                    [
                        'data' => $types->pluck('count')->toArray(),
                        'backgroundColor' => [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                        ],
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

    protected function loadProbationByStoreChart(): void
    {
        $storesData = Store::select('stores.name', DB::raw('COUNT(employees.id) as count'))
            ->leftJoin('employees', 'stores.id', '=', 'employees.store_id')
            ->whereNull('employees.ended_at')
            ->whereNull('employees.stopped_at')
            ->where('employees.probation_period', '>', 0)
            ->whereRaw('DATE_ADD(employees.started_at, INTERVAL employees.probation_period DAY) >= CURDATE()')
            ->groupBy('stores.id', 'stores.name')
            ->having('count', '>', 0)
            ->get();

        $this->probationByStoreChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $storesData->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => __('On Probation'),
                        'data' => $storesData->pluck('count')->toArray(),
                        'backgroundColor' => 'rgba(234, 179, 8, 0.8)',
                        'borderColor' => 'rgba(234, 179, 8, 1)',
                        'borderWidth' => 1,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'stepSize' => 1,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('components.layouts.admin')
            ->title(__('Dashboard'));
    }
}
