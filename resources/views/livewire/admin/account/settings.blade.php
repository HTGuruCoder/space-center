@use(App\Helpers\DateHelper)

<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Account Settings') }}</h1>
        <p class="text-base-content/70 mt-1">{{ __('Manage your personal account settings and preferences.') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Picture Section --}}
        <div class="lg:col-span-1">
            <x-card title="{{ __('Profile Picture') }}">
                <div class="flex flex-col items-center gap-4">
                    {{-- Profile Picture --}}
                   <img src="{{ $this->getPictureUrl() }}"
                            class="h-40 w-40 rounded-full object-cover cursor-pointer hover:scale-110 transition-[scale]"
                            onclick="this.parentElement.querySelector('input[type=file]').click()" />

                        <x-file wire:model="picture" accept="image/jpeg,image/jpg,image/png"
                            hint="{{ __('Click on image to upload. Max size: 2MB') }}" crop-after-change
                            change-text="{{ __('Change') }}" crop-text="{{ __('Crop') }}"
                            crop-title-text="{{ __('Crop image') }}" crop-cancel-text="{{ __('Cancel') }}"
                            crop-save-text="{{ __('Crop') }}">
                            <img src="{{ $this->getPictureUrl() }}" class="hidden" />
                        </x-file>

                    {{-- Action Buttons --}}
                    @if($picture)
                        <div class="flex gap-2 w-full">
                            <x-button wire:click="updatePicture" class="btn-primary btn-sm flex-1" spinner="updatePicture">
                                <x-icon name="mdi.upload" class="w-4 h-4" />
                                {{ __('Save Picture') }}
                            </x-button>
                            <x-button wire:click="$set('picture', null)" class="btn-ghost btn-sm flex-1">
                                {{ __('Cancel') }}
                            </x-button>
                        </div>
                    @endif

                    {{-- Remove Picture Button --}}
                    @if(auth()->user()->picture_url && !$picture)
                        <x-button wire:click="removePicture" class="btn-error btn-outline btn-sm w-full" spinner="removePicture">
                            <x-icon name="mdi.delete" class="w-4 h-4" />
                            {{ __('Remove Picture') }}
                        </x-button>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Profile Information Section --}}
        <div class="lg:col-span-2">
            <x-card title="{{ __('Profile Information') }}">
                <x-form wire:submit="updateProfile">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- First Name --}}
                        <x-input
                            label="{{ __('First Name') }}"
                            wire:model="profileForm.first_name"
                            required
                        />

                        {{-- Last Name --}}
                        <x-input
                            label="{{ __('Last Name') }}"
                            wire:model="profileForm.last_name"
                            required
                        />

                        {{-- Email --}}
                        <x-input
                            label="{{ __('Email') }}"
                            type="email"
                            wire:model="profileForm.email"
                            required
                        />

                        {{-- Country --}}
                        <x-select
                            label="{{ __('Country') }}"
                            :options="$countries"
                            wire:model="profileForm.country_code"
                            placeholder="{{ __('Select a country') }}"
                        />

                        {{-- Phone Number --}}
                        <x-input
                            label="{{ __('Phone Number') }}"
                            wire:model="profileForm.phone_number"
                            required
                        />

                        {{-- Birth Date --}}
                        <x-input
                            label="{{ __('Birth Date') }}"
                            type="date"
                            wire:model="profileForm.birth_date"
                        />

                        {{-- Timezone --}}
                        <x-select
                            label="{{ __('Timezone') }}"
                            :options="$timezones"
                            wire:model="profileForm.timezone"
                            required
                        />

                        {{-- Currency --}}
                        <x-select
                            label="{{ __('Currency') }}"
                            :options="$currencies"
                            wire:model="profileForm.currency_code"
                            required
                        />
                    </div>

                    <x-slot:actions>
                        <x-button type="submit" class="btn-primary" spinner="updateProfile">
                            <x-icon name="mdi.content-save" class="w-5 h-5" />
                            {{ __('Save Changes') }}
                        </x-button>
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>

    {{-- Password Change Section --}}
    <div class="mt-6">
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold">{{ __('Change Password') }}</h3>
                    <p class="text-sm text-base-content/70">{{ __('Update your password to keep your account secure.') }}</p>
                </div>
                @if(!$showPasswordSection)
                    <x-button wire:click="$set('showPasswordSection', true)" class="btn-primary btn-sm">
                        <x-icon name="mdi.lock-reset" class="w-4 h-4" />
                        {{ __('Change Password') }}
                    </x-button>
                @endif
            </div>

            @if($showPasswordSection)
                <x-form wire:submit="updatePassword">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Current Password --}}
                        <x-input
                            label="{{ __('Current Password') }}"
                            type="password"
                            wire:model="passwordForm.current_password"
                            required
                        />

                        {{-- New Password --}}
                        <x-input
                            label="{{ __('New Password') }}"
                            type="password"
                            wire:model="passwordForm.new_password"
                            required
                        />

                        {{-- Confirm New Password --}}
                        <x-input
                            label="{{ __('Confirm New Password') }}"
                            type="password"
                            wire:model="passwordForm.new_password_confirmation"
                            required
                        />
                    </div>

                    <x-slot:actions>
                        <x-button type="submit" class="btn-primary" spinner="updatePassword">
                            <x-icon name="mdi.check" class="w-5 h-5" />
                            {{ __('Update Password') }}
                        </x-button>
                        <x-button wire:click="$set('showPasswordSection', false)" class="btn-ghost">
                            {{ __('Cancel') }}
                        </x-button>
                    </x-slot:actions>
                </x-form>
            @endif
        </x-card>
    </div>

    {{-- Account Information --}}
    <div class="mt-6">
        <x-card title="{{ __('Account Information') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">{{ __('Account Created') }}</div>
                    <div class="stat-value text-lg">{{ DateHelper::formatDate(auth()->user()->created_at) }}</div>
                    <div class="stat-desc">{{ auth()->user()->created_at->timezone(auth()->user()->timezone)->diffForHumans() }}</div>
                </div>

                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">{{ __('Last Updated') }}</div>
                    <div class="stat-value text-lg">{{ DateHelper::formatDate(auth()->user()->updated_at) }}</div>
                    <div class="stat-desc">{{ auth()->user()->updated_at->timezone(auth()->user()->timezone)->diffForHumans() }}</div>
                </div>
            </div>
        </x-card>
    </div>
</div>
