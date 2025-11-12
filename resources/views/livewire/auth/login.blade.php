<div class="min-h-screen flex items-center justify-center relative">
    {{-- Language Switcher --}}
    <div class="absolute top-4 right-4">
        <x-layouts.partials.language-switcher />
    </div>

    <x-card class="w-full max-w-md" title="{{ __('Login') }}">
        <x-form wire:submit="login">
            <x-input
                label="{{ __('Email') }}"
                wire:model="form.email"
                icon="mdi.email"
                placeholder="{{ __('Enter your email') }}"
                inline
            />

            <x-password
                label="{{ __('Password') }}"
                wire:model="form.password"
                icon="mdi.lock"
                placeholder="{{ __('Enter your password') }}"
                right
                inline
            />

            <x-checkbox
                label="{{ __('Remember me') }}"
                wire:model="form.remember"
            />

            <x-slot:actions>
                <div class="flex flex-col gap-4 w-full">
                    <x-button
                        type="submit"
                        class="btn-primary w-full"
                        spinner="login"
                    >
                        {{ __('Login') }}
                    </x-button>

                    <div class="text-center">
                        <span class="text-sm">{{ __("Don't have an account?") }}</span>
                        <a href="{{ route('register') }}" class="link link-primary text-sm">
                            {{ __('Register here') }}
                        </a>
                    </div>
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
