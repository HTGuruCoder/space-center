<x-drawer wire:model="showDrawer" :title="$form->isEditMode ? __('Edit Employee Profile') : __('Complete Employee Profile')" right class="w-full sm:w-[600px] lg:w-2/3 max-w-full" separator
    with-close-button>
    <x-form wire:submit="save">
        <div class="space-y-4">
            {{-- Collapse 1: Employment Information --}}
            <x-collapse id="employment-info" wire:model="showEmploymentInfo" class="bg-base-200">
                <x-slot:heading>
                    <div class="flex items-center gap-2">
                        <x-icon name="mdi.briefcase" class="w-5 h-5 text-primary" />
                        <span class="font-semibold">{{ __('Employment Information') }}</span>
                    </div>
                </x-slot:heading>
                <x-slot:content>
                    <div class="space-y-6 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Position --}}
                            <x-choices-offline label="{{ __('Position') }}" :options="$positions"
                                wire:model="form.position_id" icon="mdi.briefcase"
                                placeholder="{{ __('Select position') }}" single searchable required />

                            {{-- Store --}}
                            <x-choices-offline label="{{ __('Store') }}" :options="$stores" wire:model="form.store_id"
                                icon="mdi.store" placeholder="{{ __('Select store') }}" single searchable required />

                            {{-- Manager --}}
                            <x-choices-offline label="{{ __('Manager') }}" :options="$managers"
                                wire:model="form.manager_id" icon="mdi.account-supervisor"
                                placeholder="{{ __('Select manager') }}" single searchable />

                            {{-- Contract Type --}}
                            <x-choices-offline label="{{ __('Contract Type') }}" :options="$contractTypes"
                                wire:model="form.type" icon="mdi.file-document"
                                placeholder="{{ __('Select contract type') }}" single searchable required />

                            {{-- Compensation Amount --}}
                            <x-input label="{{ __('Compensation Amount') }}" wire:model="form.compensation_amount"
                                type="number" step="0.01" min="0" icon="mdi.currency-usd"
                                placeholder="{{ __('0.00') }}" required />

                            {{-- Compensation Unit --}}
                            <x-choices-offline label="{{ __('Compensation Unit') }}" :options="$compensationUnits"
                                wire:model="form.compensation_unit" icon="mdi.calendar-clock"
                                placeholder="{{ __('Select compensation unit') }}" single searchable required />

                            {{-- Started At --}}
                            <x-datepicker label="{{ __('Started At') }}" wire:model="form.started_at"
                                icon="mdi.calendar-start" placeholder="{{ __('Select start date') }}" required />

                            {{-- Ended At --}}
                            <x-datepicker label="{{ __('Ended At') }}" wire:model="form.ended_at"
                                icon="mdi.calendar-end" placeholder="{{ __('Select end date') }}" />

                            {{-- Probation Period (days) --}}
                            <x-input label="{{ __('Probation Period (days)') }}" wire:model="form.probation_period"
                                type="number" min="0" icon="mdi.calendar-clock"
                                placeholder="{{ __('0') }}" />

                            {{-- Contract File --}}
                            <div class="md:col-span-2">
                                <x-file wire:model="form.contract_file"
                                    accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                    hint="{{ __('Optional. PDF or Word document. Max size: 10MB') }}"
                                    change-text="{{ __('Change') }}">
                                    @if ($form->getContractFileUrl())
                                        <a href="{{ $form->getContractFileUrl() }}" target="_blank"
                                            class="link link-primary">
                                            {{ __('View Current Contract') }}
                                        </a>
                                    @else
                                        <span class="text-base-content/70">{{ __('No contract file uploaded') }}</span>
                                    @endif
                                </x-file>



                            </div>

                            <div class="md:col-span-2">
                                <div class="fieldset-legend text-xs mb-0.5">{{ __('Contract File (optional)') }}</div>

                                <img src="{{ $form->contract_file ? asset('images/pdf-uploaded.svg') : asset('images/default-pdf.svg') }}"
                                    class="h-40 rounded-lg cursor-pointer hover:opacity-80 transition-opacity hover:scale-105"
                                    onclick="this.parentElement.querySelector('input[type=file]').click()" />

                                @if ($form->contract_file_url)
                                    <a href="{{ $form->getContractFileUrl() }}" target="_blank" class="link"
                                        onclick="this.parentElement.querySelector('input[type=file]').click()">

                                        {{ __('View Current Contract') }}
                                    </a>
                                @endif

                                <x-file wire:model="form.contract_file" accept="application/pdf"
                                    hint="{{ __('We accept PDF. Max size: 5MB') }}" change-text="{{ __('Change') }}">
                                    <div class="hidden"></div>
                                </x-file>
                            </div>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            {{-- Collapse 2: Bank Details --}}
            <x-collapse id="bank-details" wire:model="showBankDetails" class="bg-base-200">
                <x-slot:heading>
                    <div class="flex items-center gap-2">
                        <x-icon name="mdi.bank" class="w-5 h-5 text-secondary" />
                        <span class="font-semibold">{{ __('Bank Details') }}</span>
                    </div>
                </x-slot:heading>
                <x-slot:content>
                    <div class="space-y-6 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Bank Name --}}
                            <x-input label="{{ __('Bank Name') }}" wire:model="form.bank_name" icon="mdi.bank"
                                placeholder="{{ __('Bank name') }}" />

                            {{-- Bank Account Number --}}
                            <x-input label="{{ __('Bank Account Number') }}" wire:model="form.bank_account_number"
                                icon="mdi.credit-card" placeholder="{{ __('Account number') }}" />
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>

        {{-- Action Buttons --}}
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center w-full gap-3">
                {{-- Cancel Button --}}
                <x-button label="{{ __('Cancel') }}" @click="$wire.closeDrawer()" class="order-last sm:order-first" />

                {{-- Primary Action Button --}}
                <x-button :label="$form->isEditMode ? __('Update') : __('Complete')" type="submit" spinner="save" class="btn-primary" />
            </div>
        </x-slot:actions>
    </x-form>
</x-drawer>
