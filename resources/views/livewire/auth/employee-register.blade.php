@use(App\Enums\ContractTypeEnum)

<div class="min-h-screen flex items-center justify-center py-8 px-4 relative">
    {{-- Language Switcher --}}
    <div class="absolute top-4 right-4">
        <x-layouts.partials.language-switcher />
    </div>

    <div class="w-full max-w-6xl">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold">{{ __('Employee Registration') }}</h2>
        </div>

        <x-card class="shadow-xl">
            <x-steps wire:model="currentStep" class="mb-10 -mx-6 -mt-6 px-6 pt-6" stepper-classes="w-full" steps-color="step-primary" >
                {{-- Step 1: Personal Information --}}
                <x-step step="1" text="{{ __('Personal Information') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                        <div class="md:col-span-2">
                            <x-file
                                label="{{ __('Profile Picture') }}"
                                wire:model="form.picture"
                                accept="image/jpeg,image/jpg,image/png"
                                hint="{{ __('Please upload a passport-style photo where your face is clearly visible. This photo will be used for face recognition. Max size: 2MB') }}"
                                crop-after-change
                                change-text="{{ __('Change') }}"
                                crop-text="{{ __('Crop') }}"
                                crop-title-text="{{ __('Crop image') }}"
                                crop-cancel-text="{{ __('Cancel') }}"
                                crop-save-text="{{ __('Crop') }}"
                            >
                                <img src="{{ $form->picture ?? asset('images/default-avatar.svg') }}" class="h-40 rounded-lg" />
                            </x-file>
                        </div>

                        <x-input
                            label="{{ __('First Name') }}"
                            wire:model="form.first_name"
                            icon="mdi.account"
                            placeholder="{{ __('Herman') }}"
                            inline
                        />

                        <x-input
                            label="{{ __('Last Name') }}"
                            wire:model="form.last_name"
                            icon="mdi.account"
                            placeholder="{{ __('TCHETCHE') }}"
                            inline
                        />

                        <x-input
                            label="{{ __('Email') }}"
                            wire:model="form.email"
                            icon="mdi.email"
                            placeholder="{{ __('john.doe@example.com') }}"
                            inline
                            class="md:col-span-2"
                        />

                        <x-password
                            label="{{ __('Password') }}"
                            wire:model="form.password"
                            icon="mdi.lock"
                            placeholder="{{ __('Minimum 8 characters') }}"
                            right
                            inline
                        />

                        <x-password
                            label="{{ __('Confirm Password') }}"
                            wire:model="form.password_confirmation"
                            icon="mdi.lock-check"
                            placeholder="{{ __('Re-enter password') }}"
                            right
                            inline
                        />

                        <x-choices-offline
                            :options="$countries"
                            wire:model="form.country_code"
                            icon="mdi.flag"
                            placeholder="{{ __('Select country') }}"
                            single
                            searchable
                        />

                        <x-input
                            label="{{ __('Phone Number') }}"
                            wire:model="form.phone_number"
                            icon="mdi.phone"
                            placeholder="{{ __('+1234567890') }}"
                            inline
                            class="md:col-span-2"
                        />

                        <x-choices-offline
                            :options="$timezones"
                            wire:model="form.timezone"
                            icon="mdi.clock-outline"
                            placeholder="{{ __('Select timezone') }}"
                            single
                            searchable
                        />

                        <x-datepicker
                            label="{{ __('Birth Date') }}"
                            wire:model="form.birth_date"
                            icon="mdi.calendar"
                            placeholder="{{ __('Select birth date') }}"
                            inline
                        />


                        <x-choices-offline
                            :options="$currencies"
                            wire:model="form.currency_code"
                            icon="mdi.currency-usd"
                            placeholder="{{ __('Select currency') }}"
                            single
                            searchable
                        />
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

                {{-- Step 2: Store Information --}}
                <x-step step="2" text="{{ __('Store Information') }}">
                    <div class="grid grid-cols-1 gap-6 mt-8">
                        <x-choices-offline
                            :options="$stores"
                            wire:model="form.store_id"
                            icon="mdi.store"
                            placeholder="{{ __('Select store') }}"
                            single
                            searchable
                        />

                        <x-choices-offline
                            :options="$positions"
                            wire:model="form.position_id"
                            icon="mdi.briefcase"
                            placeholder="{{ __('Select position') }}"
                            single
                            searchable
                        />

                        <x-choices-offline
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

                {{-- Step 3: Contract Information --}}
                <x-step step="3" text="{{ __('Contract Information') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <x-choices-offline
                            :options="$contractTypes"
                            wire:model.live="form.type"
                            icon="mdi.file-document"
                            placeholder="{{ __('Select contract type') }}"
                            single
                        />

                        <x-choices-offline
                            :options="$compensationUnits"
                            wire:model="form.compensation_unit"
                            icon="mdi.clock-time-four"
                            placeholder="{{ __('Select compensation unit') }}"
                            single
                        />

                        <x-input
                            label="{{ __('Compensation Amount') }}"
                            wire:model="form.compensation_amount"
                            type="number"
                            step="0.01"
                            placeholder="{{ __('0.00') }}"
                            inline
                            money
                            locale="{{ app()->getLocale() }}"
                            prefix="{{ $form->currency_code ?? 'USD' }}"
                        />

                        <x-datepicker
                            label="{{ __('Start Date') }}"
                            wire:model="form.started_at"
                            icon="mdi.calendar-start"
                            placeholder="{{ __('Select start date') }}"
                            inline
                        />

                        @if($form->type === ContractTypeEnum::FIXED_TERM->value)
                            <x-datepicker
                                label="{{ __('End Date') }}"
                                wire:model="form.ended_at"
                                icon="mdi.calendar-end"
                                placeholder="{{ __('Select end date') }}"
                                inline
                            />
                        @endif

                        <x-input
                            label="{{ __('Probation Period (days)') }}"
                            wire:model="form.probation_period"
                            type="number"
                            icon="mdi.timer-sand"
                            placeholder="{{ __('90') }}"
                            inline
                        />

                        <x-input
                            label="{{ __('Bank Name') }}"
                            wire:model="form.bank_name"
                            icon="mdi.bank"
                            placeholder="{{ __('Bank of America') }}"
                            inline
                        />

                        <x-input
                            label="{{ __('Bank Account Number') }}"
                            wire:model="form.bank_account_number"
                            icon="mdi.credit-card"
                            placeholder="{{ __('1234567890') }}"
                            inline
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
