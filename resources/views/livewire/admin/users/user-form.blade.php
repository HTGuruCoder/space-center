<x-drawer
    wire:model="showDrawer"
    :title="$form->isEditMode ? __('Edit User') : __('Create User')"
    right
    class="w-full sm:w-[600px] lg:w-2/3 max-w-full"
    separator
    with-close-button
>
    <x-form wire:submit="save">
        <div class="space-y-4">
            {{-- Collapse 1: Personal Information --}}
            <x-collapse wire:model="showPersonalInfo" class="bg-base-200">
                <x-slot:heading>
                    <div class="flex items-center gap-2">
                        <x-icon name="mdi.account-circle" class="w-5 h-5 text-primary" />
                        <span class="font-semibold">{{ __('Personal Information') }}</span>
                    </div>
                </x-slot:heading>
s                <x-slot:content>
                    <div class="space-y-6 p-4">
                        {{-- Profile Picture --}}
                        <div>
                            {{ $form->picture }}
                            <x-file
                                label="{{ __('Profile Picture') }}"
                                wire:model="form.picture"
                                accept="image/jpeg,image/jpg,image/png"
                                hint="{{ __('Optional. Max size: 2MB') }}"
                                crop-after-change
                                change-text="{{ __('Change') }}"
                                crop-text="{{ __('Crop') }}"
                                crop-title-text="{{ __('Crop image') }}"
                                crop-cancel-text="{{ __('Cancel') }}"
                                crop-save-text="{{ __('Crop') }}"
                            >
                                <img
                                    src="{{ $form->picture ?? asset('images/default-avatar.svg') }}"
                                    class="h-40 w-40 rounded-full object-cover"
                                />
                            </x-file>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- First Name --}}
                            <x-input
                                label="{{ __('First Name') }}"
                                wire:model="form.first_name"
                                icon="mdi.account"
                                placeholder="{{ __('John') }}"
                                inline
                            />

                            {{-- Last Name --}}
                            <x-input
                                label="{{ __('Last Name') }}"
                                wire:model="form.last_name"
                                icon="mdi.account"
                                placeholder="{{ __('Doe') }}"
                                inline
                            />

                            {{-- Email --}}
                            <x-input
                                label="{{ __('Email') }}"
                                wire:model="form.email"
                                type="email"
                                icon="mdi.email"
                                placeholder="{{ __('john.doe@example.com') }}"
                                inline
                                class="md:col-span-2"
                            />

                            {{-- Password (only in create mode) --}}
                            @if(!$form->isEditMode)
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
                            @endif

                            {{-- Phone Number --}}
                            <x-input
                                label="{{ __('Phone Number') }}"
                                wire:model="form.phone_number"
                                icon="mdi.phone"
                                placeholder="{{ __('+1234567890') }}"
                                inline
                                class="md:col-span-2"
                            />

                            {{-- Country --}}
                            <x-choices-offline
                                :options="$countries"
                                wire:model="form.country_code"
                                icon="mdi.flag"
                                placeholder="{{ __('Select country') }}"
                                single
                                searchable
                            />

                            {{-- Timezone --}}
                            <x-choices-offline
                                :options="$timezones"
                                wire:model="form.timezone"
                                icon="mdi.clock-outline"
                                placeholder="{{ __('Select timezone') }}"
                                single
                                searchable
                            />

                            {{-- Birth Date --}}
                            <x-datepicker
                                label="{{ __('Birth Date') }}"
                                wire:model="form.birth_date"
                                icon="mdi.calendar"
                                placeholder="{{ __('Select birth date') }}"
                                inline
                            />

                            {{-- Currency --}}
                            <x-choices-offline
                                :options="$currencies"
                                wire:model="form.currency_code"
                                icon="mdi.currency-usd"
                                placeholder="{{ __('Select currency') }}"
                                single
                                searchable
                            />
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            {{-- Collapse 2: Roles & Permissions --}}
            <x-collapse wire:model="showRoles" class="bg-base-200">
                <x-slot:heading>
                    <div class="flex items-center gap-2">
                        <x-icon name="mdi.shield-account" class="w-5 h-5 text-secondary" />
                        <span class="font-semibold">{{ __('Roles & Permissions') }}</span>
                        @if(count($form->selectedRoles) > 0)
                            <span class="badge badge-primary badge-sm">{{ count($form->selectedRoles) }} {{ __('selected') }}</span>
                        @endif
                    </div>
                </x-slot:heading>
                <x-slot:content>
                    <div class="space-y-4 p-4">
                        @if($errors->has('selectedRoles'))
                            <div class="alert alert-error">
                                <x-icon name="mdi.alert-circle" class="w-5 h-5" />
                                <span>{{ $errors->first('selectedRoles') }}</span>
                            </div>
                        @endif

                        <div class="text-sm text-base-content/70 mb-4">
                            {{ __('Select at least one role. Employee details can be completed in the Employees section.') }}
                        </div>

                        <div class="space-y-2">
                            @foreach($roles as $role)
                                <x-checkbox
                                    label="{{ $role['label'] }}"
                                    wire:model="form.selectedRoles"
                                    value="{{ $role['name'] }}"
                                    hint="{{ trans_choice('{1} :count permission|[2,*] :count permissions', $role['permissions_count'], ['count' => $role['permissions_count']]) }}"
                                />
                            @endforeach
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>

        {{-- Action Buttons --}}
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center w-full gap-3">
                {{-- Cancel Button --}}
                <x-button
                    label="{{ __('Cancel') }}"
                    @click="$wire.closeDrawer()"
                    class="order-last sm:order-first"
                />

                {{-- Primary Action Button --}}
                <x-button
                    :label="$form->isEditMode ? __('Update') : __('Create')"
                    type="submit"
                    spinner="save"
                    class="btn-primary"
                />
            </div>
        </x-slot:actions>
    </x-form>
</x-drawer>
