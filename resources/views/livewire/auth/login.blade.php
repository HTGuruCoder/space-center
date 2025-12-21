<div class="min-h-screen flex items-center justify-center py-8 px-4 relative">
    {{-- Theme & Language Switcher --}}
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" />
        <x-layouts.partials.language-switcher />
    </div>

    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold">{{ __('Admin Login') }}</h2>
            <div class="mt-4">
                <span class="text-sm">{{ __('Employee?') }}</span>
                <a href="{{ route('login') }}" class="link link-primary text-sm">
                    {{ __('Login here') }}
                </a>
            </div>
        </div>

        <x-card class="shadow-xl">
            <x-form wire:submit="login">
                <x-input
                    label="{{ __('Email') }}"
                    wire:model="form.email"
                    icon="mdi.email"
                    placeholder="{{ __('Enter your email') }}"
                    required
                />

                <x-password
                    label="{{ __('Password') }}"
                    wire:model="form.password"
                    icon="mdi.lock"
                    placeholder="{{ __('Enter your password') }}"
                    right
                    required
                />

                <x-slot:actions>
                    <x-button
                        type="submit"
                        class="btn-primary w-full"
                        spinner="login"
                    >
                        {{ __('Login') }}
                    </x-button>
                </x-slot:actions>
            </x-form>
        </x-card>
    </div>
</div>
