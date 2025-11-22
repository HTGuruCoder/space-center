@php
    $hasChildren = isset($node['children']) && count($node['children']) > 0;
@endphp

<div class="org-node {{ $hasChildren ? 'has-children' : '' }}">
    {{-- Employee Card --}}
    <div class="org-node-card {{ $node['is_current'] ? 'current-user' : '' }}">
        {{-- Avatar --}}
        <div class="flex justify-center mb-3">
            @if($node['avatar'])
                <div class="avatar">
                    <div class="w-16 h-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                        <img src="{{ $node['avatar'] }}" alt="{{ $node['name'] }}" />
                    </div>
                </div>
            @else
                <div class="avatar placeholder">
                    <div class="w-16 h-16 rounded-full bg-primary text-primary-content ring ring-primary ring-offset-base-100 ring-offset-2">
                        <span class="text-xl font-bold">
                            {{ $node['initials'] }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Name --}}
        <h3 class="font-bold text-base mb-1">{{ $node['name'] }}</h3>

        {{-- Position --}}
        <p class="text-sm text-base-content/70 mb-1">{{ $node['position'] }}</p>

        {{-- Store --}}
        <p class="text-xs text-base-content/60">{{ $node['store'] }}</p>

        {{-- Current User Badge --}}
        @if($node['is_current'])
            <div class="mt-2">
                <span class="badge badge-primary badge-sm">{{ __('You') }}</span>
            </div>
        @endif

        {{-- Subordinate Count --}}
        @if($hasChildren)
            <div class="mt-2">
                <span class="badge badge-ghost badge-sm">
                    <x-icon name="mdi.account-group" class="w-3 h-3 mr-1" />
                    {{ count($node['children']) }} {{ count($node['children']) === 1 ? __('subordinate') : __('subordinates') }}
                </span>
            </div>
        @endif
    </div>

    {{-- Children --}}
    @if($hasChildren)
        <div class="org-children">
            @foreach($node['children'] as $child)
                @include('livewire.employee.subordinates.partials.org-node', ['node' => $child, 'isRoot' => false])
            @endforeach
        </div>
    @endif
</div>
