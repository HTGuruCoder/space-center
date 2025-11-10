<ul class="menu menu-horizontal px-1">
    <li>
        <a href="{{ route('employees.dashboard') }}" @class(['active' => request()->routeIs('employees.dashboard')])>
            <x-icon name="mdi.view-dashboard" class="w-5 h-5" />
            <span class="hidden lg:inline">{{ __('Dashboard') }}</span>
        </a>
    </li>

    <li>
        <a href="{{ route('employees.subordinates.list') }}" @class(['active' => request()->routeIs('employees.subordinates.*')])>
            <x-icon name="mdi.account-group" class="w-5 h-5" />
            <span class="hidden lg:inline">{{ __('Employees') }}</span>
        </a>
    </li>

    <li>
        <a href="{{ route('employees.weekly-schedule') }}" @class(['active' => request()->routeIs('employees.weekly-schedule')])>
            <x-icon name="mdi.calendar-week" class="w-5 h-5" />
            <span class="hidden lg:inline">{{ __('Weekly Schedule') }}</span>
        </a>
    </li>

    <li>
        <a href="{{ route('employees.calendar') }}" @class(['active' => request()->routeIs('employees.calendar')])>
            <x-icon name="mdi.calendar" class="w-5 h-5" />
            <span class="hidden lg:inline">{{ __('Calendar') }}</span>
        </a>
    </li>
</ul>
