<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'SpaceCenter') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-base-200 overflow-x-hidden" x-data="{ drawer: false }">
    {{-- Mobile Drawer Backdrop --}}
    <div x-show="drawer" x-cloak @click="drawer = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Mobile Drawer --}}
    <div x-show="drawer" x-cloak
        class="fixed inset-y-0 left-0 z-50 w-64 bg-base-100 shadow-xl lg:hidden overflow-y-auto"
        x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
        <div class="p-4">
            {{-- Close button --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <x-icon name="mdi.shield-crown" class="w-8 h-8 text-primary" />
                    <div>
                        <span class="text-xl font-bold">SpaceCenter</span>
                        <span class="badge badge-primary badge-sm ml-2">Admin</span>
                    </div>
                </div>
                <button @click="drawer = false" class="btn btn-ghost btn-sm btn-circle">
                    <x-icon name="mdi.close" class="w-5 h-5" />
                </button>
            </div>

            <x-layouts.partials.admin-nav-sidebar />
        </div>
    </div>

    <div class="flex min-h-screen">
        {{-- Sidebar for Desktop --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-base-100 border-r border-base-300">
            {{-- Logo --}}
            <div class="p-6 border-b border-base-300">
                <a href="{{ route('admins.dashboard') }}" class="flex items-center gap-3">
                    <x-icon name="mdi.shield-crown" class="w-8 h-8 text-primary" />
                    <div>
                        <span class="text-xl font-bold block">SpaceCenter</span>
                        <span class="text-xs text-base-content/60">Admin Panel</span>
                    </div>
                </a>
            </div>

            {{-- Navigation Menu --}}
            <div class="flex-1 overflow-y-auto p-4">
                <x-layouts.partials.admin-nav-sidebar />
            </div>

            {{-- Footer in Sidebar --}}
            <div class="p-4 border-t border-base-300">
                <p class="text-xs text-center text-base-content/60">
                    {{ __('© :year SpaceCenter', ['year' => date('Y')]) }}
                </p>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex flex-col">
            {{-- Top Header --}}
            <header class="bg-base-100 border-b border-base-300 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 py-3">
                    {{-- Mobile Menu Button --}}
                    <button @click="drawer = true" class="btn btn-ghost btn-sm lg:hidden">
                        <x-icon name="mdi.menu" class="w-6 h-6" />
                    </button>

                    {{-- Page Title (optional, can be used by pages) --}}
                    <div class="flex-1 lg:flex-none">
                        @if (isset($header))
                            {{ $header }}
                        @endif
                    </div>

                    {{-- Right Side Actions --}}
                    <div class="flex items-center gap-2">
                        {{-- Theme Toggle --}}
                        <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" />

                        {{-- Language Switcher --}}
                        <x-layouts.partials.language-switcher />

                        {{-- Workspace Switcher --}}
                        <x-layouts.partials.workspace-switcher />

                        {{-- Avatar Dropdown --}}
                        <x-layouts.partials.avatar-dropdown />
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="p-4 md:p-6 lg:p-8">
                <div class="w-[calc(100vw-2*16px)] md:w-[calc(100vw-2*24px)] lg:w-[calc(100vw-256px-2*32px)]">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{-- Mary UI Toast Notifications --}}
    <x-toast />
</body>

</html>
