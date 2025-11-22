@use(App\Helpers\DateHelper)

<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Settings') }}</h1>
        <p class="text-base-content/70 mt-1">{{ __('Manage your personal settings and preferences.') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Picture Section --}}
        <div class="lg:col-span-1">
            <x-card title="{{ __('Profile Picture') }}">
                @if(!$this->hasPicture())
                    {{-- No Picture - Show Initials and Take Photo Button --}}
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative">
                            <div class="h-40 w-40 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="text-5xl font-bold text-primary">{{ auth()->user()->initials }}</span>
                            </div>
                            <div class="absolute -bottom-2 -right-2 bg-base-100 rounded-full p-2 shadow-lg">
                                <x-icon name="mdi.camera" class="w-6 h-6 text-primary" />
                            </div>
                        </div>

                        <p class="text-sm text-center text-base-content/70">
                            {{ __('Take your profile photo with face verification') }}
                        </p>

                        <x-button wire:click="openFaceCapture" class="btn-primary w-full">
                            <x-icon name="mdi.camera" class="w-5 h-5" />
                            {{ __('Take Photo') }}
                        </x-button>
                    </div>
                @else
                    {{-- Has Picture - Show Photo (LOCKED) --}}
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative">
                            <img src="{{ $this->getPictureUrl() }}"
                                 class="h-40 w-40 rounded-full object-cover ring-4 ring-success ring-offset-2" />
                            <div class="badge badge-success gap-2 absolute -top-2 -right-2 shadow-lg">
                                <x-icon name="mdi.check-decagram" class="w-4 h-4" />
                                {{ __('Verified') }}
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <x-icon name="mdi.lock" class="w-5 h-5" />
                            <span class="text-sm">
                                {{ __('Your profile photo is verified and locked. Contact admin to change it.') }}
                            </span>
                        </div>
                    </div>
                @endif
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

                        {{-- Email (READ-ONLY) --}}
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text">{{ __('Email') }}</span>
                                <span class="badge badge-neutral badge-sm gap-1">
                                    <x-icon name="mdi.lock" class="w-3 h-3" />
                                    {{ __('Locked') }}
                                </span>
                            </label>
                            <input
                                type="email"
                                value="{{ auth()->user()->email }}"
                                class="input input-bordered bg-base-200 cursor-not-allowed"
                                disabled
                                readonly
                            />
                        </div>

                        {{-- Country --}}
                        <x-select
                            label="{{ __('Country') }}"
                            :options="$countries"
                            wire:model="profileForm.country_code"
                            placeholder="{{ __('Select a country') }}"
                            required
                        />

                        {{-- Phone Number --}}
                        <x-input
                            label="{{ __('Phone Number') }}"
                            wire:model="profileForm.phone_number"
                            type="tel"
                            required
                        />

                        {{-- Birth Date --}}
                        <x-input
                            label="{{ __('Birth Date') }}"
                            type="date"
                            wire:model="profileForm.birth_date"
                            required
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
                            hint="{{ __('Minimum 8 characters') }}"
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

    {{-- Face Capture Modal --}}
    @if($showFaceCaptureModal)
        <x-modal wire:model="showFaceCaptureModal" title="{{ __('Take Profile Photo') }}" class="max-w-4xl">
            <x-face-capture-component />
        </x-modal>
    @endif
</div>
