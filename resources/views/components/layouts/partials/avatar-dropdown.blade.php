@php
$user = auth()->user();
$isEmployeeSpace = request()->routeIs('employees.*');
@endphp

<div class="dropdown dropdown-end">
    <label tabindex="0" class="btn btn-ghost btn-circle avatar">
        <div class="w-10 rounded-full">
            @if($user->getProfilePictureUrl())
                <img src="{{ $user->getProfilePictureUrl() }}" alt="{{ $user->full_name }}" class="object-cover w-full h-full" />
            @else
                <div class="bg-primary text-primary-content flex items-center justify-center w-full h-full text-sm font-bold">
                    {{ $user->initials }}
                </div>
            @endif
        </div>
    </label>

    <ul tabindex="0" class="dropdown-content menu menu-sm bg-base-100 rounded-box shadow-lg w-52 p-2 mt-2 z-50">
        {{-- User info header --}}
        <li class="menu-title">
            <span class="text-base font-semibold">{{ $user->full_name }}</span>
        </li>
        <li class="menu-title">
            <span class="text-xs opacity-60">{{ $user->email }}</span>
        </li>

        <li><hr class="my-2" /></li>

        {{-- Account Settings link --}}
        <li>
            <a href="{{ route(request()->routeIs('employees.*') ? 'employees.settings' : 'admins.account.settings') }}">
                <x-icon name="mdi.cog" class="w-5 h-5" />
                {{ __('Account Settings') }}
            </a>
        </li>

        {{-- Allowed Locations (Employee space only) --}}
        @if($isEmployeeSpace)
            <li>
                <a href="{{ route('employees.allowed-locations') }}">
                    <x-icon name="mdi.map-marker" class="w-5 h-5" />
                    {{ __('Allowed Locations') }}
                </a>
            </li>
        @endif

        <li><hr class="my-2" /></li>

        {{-- Logout --}}
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left">
                    <x-icon name="mdi.logout" class="w-5 h-5" />
                    {{ __('Logout') }}
                </button>
            </form>
        </li>
    </ul>
</div>
