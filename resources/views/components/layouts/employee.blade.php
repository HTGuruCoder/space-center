<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'SpaceCenter') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        {{-- Header/Navbar --}}
        <header class="navbar bg-base-100 shadow-md sticky top-0 z-40">
            <div class="navbar-start">
                {{-- Logo --}}
                <a href="{{ route('employees.dashboard') }}" class="btn btn-ghost text-xl">
                    <x-icon name="mdi.rocket-launch" class="w-6 h-6" />
                    <span class="hidden sm:inline">SpaceCenter</span>
                </a>
            </div>

            <div class="navbar-center hidden lg:flex">
                {{-- Employee Navigation --}}
                <x-layouts.partials.employee-nav />
            </div>

            <div class="navbar-end gap-2">
                {{-- Language Switcher --}}
                <div class="hidden md:flex">
                    <x-layouts.partials.language-switcher />
                </div>

                {{-- Workspace Switcher --}}
                <x-layouts.partials.workspace-switcher />

                {{-- Avatar Dropdown --}}
                <x-layouts.partials.avatar-dropdown />
            </div>
        </header>

        {{-- Main Content --}}
        <main class="container mx-auto px-4 py-8">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="footer footer-center p-4 bg-base-300 text-base-content mt-auto">
            <div>
                <p>{{ __('Copyright © :year - SpaceCenter', ['year' => date('Y')]) }}</p>
            </div>
        </footer>

        {{-- Mary UI Toast Notifications --}}
        <x-toast />
    </body>
</html>
