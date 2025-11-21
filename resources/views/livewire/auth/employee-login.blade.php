<div class="min-h-screen flex items-center justify-center py-8 px-4 relative">
    {{-- Theme & Language Switcher --}}
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" />
        <x-layouts.partials.language-switcher />
    </div>

    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold">{{ __('Employee Login') }}</h2>
            <div class="mt-4">
                <span class="text-sm">{{ __("Don't have an account?") }}</span>
                <a href="{{ route('employee.register') }}" class="link link-primary text-sm">
                    {{ __('Register here') }}
                </a>
            </div>
            <div class="mt-2">
                <span class="text-sm">{{ __('Admin?') }}</span>
                <a href="{{ route('admin.login') }}" class="link link-primary text-sm">
                    {{ __('Login here') }}
                </a>
            </div>
        </div>

        <x-card class="shadow-xl">
            {{-- Progress Steps --}}
            <div class="mb-8">
                <ul class="steps steps-horizontal w-full">
                    <li class="step @if($form->currentStep >= 1) step-primary @endif">
                        {{ __('Email') }}
                    </li>
                    <li class="step @if($form->currentStep >= 2) step-primary @endif">
                        {{ __('Facial Recognition') }}
                    </li>
                    <li class="step @if($form->currentStep >= 3) step-primary @endif">
                        {{ __('PIN') }}
                    </li>
                </ul>
            </div>

            {{-- Step 1: Email --}}
            @if(!$showFaceCapture && !$showPinPad)
                <div>
                    <x-input
                        label="{{ __('Email Address') }}"
                        wire:model="form.email"
                        icon="mdi.email"
                        placeholder="{{ __('john.doe@example.com') }}"
                        required
                    />

                    <div class="flex justify-end mt-6">
                        <x-button
                            wire:click="nextToFaceCapture"
                            class="btn-primary"
                            spinner="nextToFaceCapture"
                        >
                            {{ __('Next') }}
                            <x-icon name="mdi.arrow-right" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>

                    <div class="mt-6 p-3 bg-info/10 border border-info/30 rounded-lg">
                        <p class="text-sm flex items-start gap-2">
                            <x-icon name="mdi.information" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                            <span>{{ __('Employee login uses facial recognition and PIN. Make sure you have registered with your face photo.') }}</span>
                        </p>
                    </div>
                </div>
            @endif

            {{-- Step 2: Face Capture --}}
            @if($showFaceCapture)
                <div>
                    <div class="mb-4">
                        <h3 class="font-semibold text-lg">{{ __('Verify Your Face') }}</h3>
                        <p class="text-sm text-base-content/70">{{ __('Position your face in front of the camera') }}</p>
                    </div>

                    <x-face-capture-component wire-model="form.photo" />

                    <div class="flex justify-between mt-6">
                        <x-button
                            wire:click="backToEmail"
                            class="btn-outline"
                        >
                            <x-icon name="mdi.arrow-left" class="w-5 h-5 mr-2" />
                            {{ __('Back') }}
                        </x-button>

                        <x-button
                            wire:click="verifyFace"
                            class="btn-primary"
                            spinner="verifyFace"
                        >
                            {{ __('Verify Face') }}
                            <x-icon name="mdi.arrow-right" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </div>
            @endif

            {{-- Step 3: PIN --}}
            @if($showPinPad)
                <div>
                    <div class="mb-4 text-center">
                        <h3 class="font-semibold text-lg">{{ __('Enter Your PIN') }}</h3>
                        <p class="text-sm text-base-content/70">{{ __('Enter your 4-6 digit PIN code') }}</p>
                    </div>

                    <x-pin-pad-component
                        wire-model="form.pin"
                        :min-length="4"
                        :max-length="6"
                        :label="__('PIN Code')"
                    />

                    <div class="flex justify-between mt-6">
                        <x-button
                            wire:click="backToEmail"
                            class="btn-outline"
                        >
                            <x-icon name="mdi.arrow-left" class="w-5 h-5 mr-2" />
                            {{ __('Start Over') }}
                        </x-button>

                        <x-button
                            wire:click="login"
                            class="btn-primary"
                            spinner="login"
                        >
                            {{ __('Login') }}
                            <x-icon name="mdi.login" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </div>
            @endif
        </x-card>
    </div>
</div>
