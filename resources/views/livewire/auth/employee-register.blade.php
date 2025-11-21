@use(App\Enums\ContractTypeEnum)

<div class="min-h-screen flex items-center justify-center py-8 px-4 relative">
    {{-- Theme & Language Switcher --}}
    <div class="absolute top-4 right-4 flex items-center gap-2">
        <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" />
        <x-layouts.partials.language-switcher />
    </div>

    <div class="w-full max-w-6xl">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold">{{ __('Employee Registration') }}</h2>
            <div class="mt-4">
                <span class="text-sm">{{ __('Already have an account?') }}</span>
                <a href="{{ route('login') }}" class="link link-primary text-sm">
                    {{ __('Login here') }}
                </a>
            </div>
        </div>

        <x-card class="shadow-xl">
            <x-steps wire:model="currentStep" class="mb-10 -mx-6 -mt-6 px-6 pt-6" stepper-classes="w-full" steps-color="step-primary" >
                {{-- Step 1: Face Capture --}}
                <x-step step="1" text="{{ __('Face Capture') }}">
                    <div class="mt-8">
                        <label class="label">
                            <span class="label-text font-semibold">{{ __('Facial Recognition Photo') }}</span>
                        </label>
                        <x-face-capture-component wire-model="form.photo" />

                        {{-- Photo Examples --}}
                        <div class="mt-6 p-4 bg-base-200 rounded-lg">
                            <h3 class="font-semibold mb-4 flex items-center gap-2">
                                <x-icon name="mdi.information" class="w-5 h-5 text-info" />
                                {{ __('Photo Guidelines') }}
                            </h3>

                            {{-- Visual Examples with Real Photos --}}
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                                {{-- Good Example 1: Front Facing --}}
                                <div class="text-center">
                                    <div class="relative">
                                        <img
                                            src="{{ asset('images/photo-examples/good-front-facing.jpg') }}"
                                            alt="{{ __('Good example: Front facing') }}"
                                            class="aspect-square w-full object-contain rounded-lg border-2 border-success bg-base-200"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';"
                                        />
                                        <div class="absolute -top-2 -right-2 bg-success rounded-full p-1.5 shadow-lg">
                                            <x-icon name="mdi.check" class="w-5 h-5 text-white" />
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 bg-success/90 text-white px-2 py-1 rounded-b-lg">
                                            <p class="text-xs font-medium">{{ __('Front facing') }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs mt-2 text-success font-semibold">✓ {{ __('Good') }}</p>
                                </div>

                                {{-- Good Example 2: Well Lit --}}
                                <div class="text-center">
                                    <div class="relative">
                                        <img
                                            src="{{ asset('images/photo-examples/good-lighting.jpg') }}"
                                            alt="{{ __('Good example: Well lit') }}"
                                            class="aspect-square w-full object-contain rounded-lg border-2 border-success bg-base-200"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';"
                                        />
                                        <div class="absolute -top-2 -right-2 bg-success rounded-full p-1.5 shadow-lg">
                                            <x-icon name="mdi.check" class="w-5 h-5 text-white" />
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 bg-success/90 text-white px-2 py-1 rounded-b-lg">
                                            <p class="text-xs font-medium">{{ __('Good lighting') }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs mt-2 text-success font-semibold">✓ {{ __('Good') }}</p>
                                </div>

                                {{-- Bad Example 1: Sunglasses --}}
                                <div class="text-center">
                                    <div class="relative">
                                        <img
                                            src="{{ asset('images/photo-examples/bad-sunglasses.jpg') }}"
                                            alt="{{ __('Bad example: Sunglasses') }}"
                                            class="aspect-square w-full object-contain rounded-lg border-2 border-error bg-base-200"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';"
                                        />
                                        <div class="absolute -top-2 -right-2 bg-error rounded-full p-1.5 shadow-lg">
                                            <x-icon name="mdi.close" class="w-5 h-5 text-white" />
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 bg-error/90 text-white px-2 py-1 rounded-b-lg">
                                            <p class="text-xs font-medium">{{ __('Sunglasses') }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs mt-2 text-error font-semibold">✗ {{ __('Bad') }}</p>
                                </div>

                                {{-- Bad Example 2: Group Photo --}}
                                <div class="text-center">
                                    <div class="relative">
                                        <img
                                            src="{{ asset('images/photo-examples/bad-group.jpg') }}"
                                            alt="{{ __('Bad example: Group photo') }}"
                                            class="aspect-square w-full object-contain rounded-lg border-2 border-error bg-base-200"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.svg') }}';"
                                        />
                                        <div class="absolute -top-2 -right-2 bg-error rounded-full p-1.5 shadow-lg">
                                            <x-icon name="mdi.close" class="w-5 h-5 text-white" />
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 bg-error/90 text-white px-2 py-1 rounded-b-lg">
                                            <p class="text-xs font-medium">{{ __('Group photo') }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs mt-2 text-error font-semibold">✗ {{ __('Bad') }}</p>
                                </div>
                            </div>

                            {{-- Detailed Guidelines --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Good Photos --}}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 text-success font-medium">
                                        <x-icon name="mdi.check-circle" class="w-5 h-5" />
                                        {{ __('Good Photos') }}
                                    </div>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Face clearly visible and centered') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Good lighting (avoid shadows)') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Neutral background') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Looking straight at camera') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.check" class="w-4 h-4 text-success mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Passport-style or professional headshot') }}</span>
                                        </li>
                                    </ul>
                                </div>

                                {{-- Bad Photos --}}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 text-error font-medium">
                                        <x-icon name="mdi.close-circle" class="w-5 h-5" />
                                        {{ __('Avoid') }}
                                    </div>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.close" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Sunglasses or face covered') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.close" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Blurry or low quality images') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.close" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Group photos or multiple people') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.close" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Side angle or profile shots') }}</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <x-icon name="mdi.close" class="w-4 h-4 text-error mt-0.5 flex-shrink-0" />
                                            <span>{{ __('Heavy filters or edited photos') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-info/10 border border-info/30 rounded-lg">
                                <p class="text-sm flex items-start gap-2">
                                    <x-icon name="mdi.lightbulb" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
                                    <span>{{ __('Tip: A clear, well-lit photo will ensure accurate face recognition for attendance tracking.') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-8 pt-6 border-t border-base-300">
                        <x-button
                            wire:click="nextStep"
                            class="btn-primary"
                            spinner="nextStep"
                        >
                            {{ __('Next') }}
                            <x-icon name="mdi.arrow-right" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </x-step>

                {{-- Step 2: PIN --}}
                <x-step step="2" text="{{ __('PIN Code') }}">
                    <div class="mt-8">
                        <x-pin-pad-component
                            wire-model="form.pin"
                            :min-length="4"
                            :max-length="6"
                            :label="__('Create PIN Code (4-6 digits)')"
                        />
                    </div>

                    <div class="flex justify-between mt-8 pt-6 border-t border-base-300">
                        <x-button
                            wire:click="previousStep"
                            class="btn-outline"
                        >
                            <x-icon name="mdi.arrow-left" class="w-5 h-5 mr-2" />
                            {{ __('Previous') }}
                        </x-button>

                        <x-button
                            wire:click="nextStep"
                            class="btn-primary"
                            spinner="nextStep"
                        >
                            {{ __('Next') }}
                            <x-icon name="mdi.arrow-right" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </x-step>

                {{-- Step 3: Personal Information --}}
                <x-step step="3" text="{{ __('Personal Information') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <x-input
                            label="{{ __('First Name') }}"
                            wire:model="form.first_name"
                            icon="mdi.account"
                            placeholder="{{ __('Herman') }}"
                            required
                        />

                        <x-input
                            label="{{ __('Last Name') }}"
                            wire:model="form.last_name"
                            icon="mdi.account"
                            placeholder="{{ __('TCHETCHE') }}"
                            required
                        />

                        <x-input
                            label="{{ __('Email') }}"
                            wire:model="form.email"
                            icon="mdi.email"
                            placeholder="{{ __('john.doe@example.com') }}"
                            required
                            class="md:col-span-2"
                        />

                        <x-choices-offline
                            label="{{ __('Country') }}"
                            :options="$countries"
                            wire:model="form.country_code"
                            icon="mdi.flag"
                            placeholder="{{ __('Select country') }}"
                            single
                            searchable
                            required
                        />

                        <x-input
                            label="{{ __('Phone Number') }}"
                            wire:model="form.phone_number"
                            icon="mdi.phone"
                            placeholder="{{ __('+1234567890') }}"
                            required
                        />

                        <x-choices-offline
                            label="{{ __('Timezone') }}"
                            :options="$timezones"
                            wire:model="form.timezone"
                            icon="mdi.clock-outline"
                            placeholder="{{ __('Select timezone') }}"
                            single
                            searchable
                            required
                        />

                        <x-datepicker
                            label="{{ __('Birth Date') }}"
                            wire:model="form.birth_date"
                            icon="mdi.calendar"
                            placeholder="{{ __('Select birth date') }}"
                            required
                        />

                        <x-choices-offline
                            label="{{ __('Currency') }}"
                            :options="$currencies"
                            wire:model="form.currency_code"
                            icon="mdi.currency-usd"
                            placeholder="{{ __('Select currency') }}"
                            single
                            searchable
                            required
                        />
                    </div>

                    <div class="flex justify-between mt-8 pt-6 border-t border-base-300">
                        <x-button
                            wire:click="previousStep"
                            class="btn-outline"
                        >
                            <x-icon name="mdi.arrow-left" class="w-5 h-5 mr-2" />
                            {{ __('Previous') }}
                        </x-button>

                        <x-button
                            wire:click="nextStep"
                            class="btn-primary"
                            spinner="nextStep"
                        >
                            {{ __('Next') }}
                            <x-icon name="mdi.arrow-right" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </x-step>

                {{-- Step 4: Store Information --}}
                <x-step step="4" text="{{ __('Store Information') }}">
                    <div class="grid grid-cols-1 gap-6 mt-8">
                        <x-choices-offline
                            label="{{ __('Store') }}"
                            :options="$stores"
                            wire:model="form.store_id"
                            icon="mdi.store"
                            placeholder="{{ __('Select store') }}"
                            single
                            searchable
                            required
                        />

                        <x-choices-offline
                            label="{{ __('Position') }}"
                            :options="$positions"
                            wire:model="form.position_id"
                            icon="mdi.briefcase"
                            placeholder="{{ __('Select position') }}"
                            single
                            searchable
                            required
                        />

                        <x-choices-offline
                            label="{{ __('Manager') }}"
                            :options="$managers"
                            wire:model="form.manager_id"
                            icon="mdi.account-tie"
                            placeholder="{{ __('Select manager (optional)') }}"
                            single
                            searchable
                            hint="{{ __('Optional') }}"
                        />
                    </div>

                    <div class="flex justify-between mt-8 pt-6 border-t border-base-300">
                        <x-button
                            wire:click="previousStep"
                            class="btn-outline"
                        >
                            <x-icon name="mdi.arrow-left" class="w-5 h-5 mr-2" />
                            {{ __('Previous') }}
                        </x-button>

                        <x-button
                            wire:click="nextStep"
                            class="btn-primary"
                            spinner="nextStep"
                        >
                            {{ __('Next') }}
                            <x-icon name="mdi.arrow-right" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </x-step>

                {{-- Step 5: Contract Information --}}
                <x-step step="5" text="{{ __('Contract Information') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <x-choices-offline
                            label="{{ __('Contract Type') }}"
                            :options="$contractTypes"
                            wire:model.live="form.type"
                            icon="mdi.file-document"
                            placeholder="{{ __('Select contract type') }}"
                            single
                            required
                        />

                        <x-choices-offline
                            label="{{ __('Compensation Unit') }}"
                            :options="$compensationUnits"
                            wire:model="form.compensation_unit"
                            icon="mdi.clock-time-four"
                            placeholder="{{ __('Select compensation unit') }}"
                            single
                            required
                        />

                        <x-input
                            label="{{ __('Compensation Amount') }}"
                            wire:model="form.compensation_amount"
                            type="number"
                            step="0.01"
                            placeholder="{{ __('0.00') }}"
                            money
                            locale="{{ app()->getLocale() }}"
                            prefix="{{ $form->currency_code ?? 'USD' }}"
                            required
                        />

                        <x-datepicker
                            label="{{ __('Start Date') }}"
                            wire:model="form.started_at"
                            icon="mdi.calendar-start"
                            placeholder="{{ __('Select start date') }}"
                            required
                        />

                        @if($form->type === ContractTypeEnum::FIXED_TERM->value)
                            <x-datepicker
                                label="{{ __('End Date') }}"
                                wire:model="form.ended_at"
                                icon="mdi.calendar-end"
                                placeholder="{{ __('Select end date') }}"
                                required
                            />
                        @endif

                        <x-input
                            label="{{ __('Probation Period (days)') }}"
                            wire:model="form.probation_period"
                            type="number"
                            icon="mdi.timer-sand"
                            placeholder="{{ __('90') }}"
                            required
                        />

                        <x-input
                            label="{{ __('Bank Name') }}"
                            wire:model="form.bank_name"
                            icon="mdi.bank"
                            placeholder="{{ __('Bank of America') }}"
                            required
                        />

                        <x-input
                            label="{{ __('Bank Account Number') }}"
                            wire:model="form.bank_account_number"
                            icon="mdi.credit-card"
                            placeholder="{{ __('1234567890') }}"
                            required
                        />

                        <div class="md:col-span-2">
                            <div class="fieldset-legend text-xs mb-0.5">{{ __('Contract File (optional)') }}</div>

                            <img
                                src="{{ $form->contract_file ? asset('images/pdf-uploaded.svg') : asset('images/default-pdf.svg') }}"
                                class="h-40 rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                                onclick="this.parentElement.querySelector('input[type=file]').click()"
                            />

                            <x-file
                                wire:model="form.contract_file"
                                accept="application/pdf"
                                hint="{{ __('We accept PDF. Max size: 5MB') }}"
                                change-text="{{ __('Change') }}"
                            >
                                <div class="hidden"></div>
                            </x-file>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8 pt-6 border-t border-base-300">
                        <x-button
                            wire:click="previousStep"
                            class="btn-outline"
                        >
                            <x-icon name="mdi.arrow-left" class="w-5 h-5 mr-2" />
                            {{ __('Previous') }}
                        </x-button>

                        <x-button
                            wire:click="register"
                            class="btn-primary"
                            spinner="register"
                        >
                            {{ __('Register') }}
                            <x-icon name="mdi.check" class="w-5 h-5 ml-2" />
                        </x-button>
                    </div>
                </x-step>
            </x-steps>
        </x-card>
    </div>
</div>
