@php
$workspaces = auth()->user()->availableWorkspaces();
$currentWorkspace = auth()->user()->getCurrentWorkspace();
@endphp

@if(count($workspaces) > 1)
    <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
            <x-icon name="mdi.swap-horizontal" class="w-5 h-5" />
            <span class="hidden sm:inline">{{ __('Switch') }}</span>
            <x-icon name="mdi.chevron-down" class="w-4 h-4" />
        </label>

        <ul tabindex="0" class="dropdown-content menu menu-sm bg-base-100 rounded-box shadow-lg w-52 p-2 mt-2 z-50">
            @foreach($workspaces as $workspace)
                <li>
                    <a
                        href="{{ $workspace['url'] }}"
                        @class([
                            'flex items-center gap-2',
                            'active' => $currentWorkspace === $workspace['key']
                        ])
                    >
                        <x-icon :name="$workspace['icon']" class="w-5 h-5" />
                        <span>{{ $workspace['name'] }}</span>

                        @if($currentWorkspace === $workspace['key'])
                            <x-icon name="mdi.check" class="w-4 h-4 ml-auto text-primary" />
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
