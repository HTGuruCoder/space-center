<div>
    @php
        $uuid = 'phone-' . md5($label ?? 'phone');
    @endphp

    <div class="form-control w-full">
        @if($label)
            <label for="{{ $uuid }}" class="label">
                <span class="label-text">
                    {{ $label }}
                    @if($required)
                        <span class="text-error">*</span>
                    @endif
                </span>
            </label>
        @endif

        <div class="flex gap-2" x-data="{
            open: false,
            search: '',
            countryCode: @entangle('countryCode'),
            countries: @js($this->countryOptions),
            dialCode: @entangle('countryDialCode').live,
            get filteredCountries() {
                if (!this.search) return Object.entries(this.countries);
                return Object.entries(this.countries).filter(([code, name]) => {
                    return name.toLowerCase().includes(this.search.toLowerCase()) ||
                           code.toLowerCase().includes(this.search.toLowerCase());
                });
            },
            selectCountry(code) {
                this.countryCode = code;
                this.open = false;
                this.search = '';
            },
            getFlagUrl(code) {
                return `https://purecatamphetamine.github.io/country-flag-icons/3x2/${code.toUpperCase()}.svg`;
            }
        }">
            {{-- Country Selector --}}
            <div class="relative" @click.away="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    class="btn btn-outline h-full min-h-[3rem] px-3"
                    :disabled="@js($disabled)"
                >
                    <img
                        :src="getFlagUrl(countryCode)"
                        :alt="countryCode"
                        class="w-6 h-4 object-cover rounded"
                    >
                    <span class="text-sm" x-text="dialCode"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div
                    x-show="open"
                    x-transition
                    class="absolute z-50 mt-2 w-80 bg-base-100 rounded-lg shadow-lg border border-base-300 max-h-96 overflow-hidden flex flex-col"
                >
                    {{-- Search Input --}}
                    <div class="p-3 border-b border-base-300">
                        <input
                            type="text"
                            x-model="search"
                            placeholder="{{ __('Search country...') }}"
                            class="input input-sm input-bordered w-full"
                            @click.stop
                        >
                    </div>

                    {{-- Country List --}}
                    <div class="overflow-y-auto">
                        <template x-for="[code, name] in filteredCountries" :key="code">
                            <button
                                type="button"
                                @click="selectCountry(code)"
                                class="w-full px-4 py-2 text-left hover:bg-base-200 flex items-center gap-3"
                                :class="{ 'bg-base-200': countryCode === code }"
                            >
                                <img
                                    :src="getFlagUrl(code)"
                                    :alt="code"
                                    class="w-6 h-4 object-cover rounded"
                                >
                                <span class="flex-1 text-sm" x-text="name"></span>
                                <span class="text-xs text-base-content/60" x-text="code"></span>
                            </button>
                        </template>

                        {{-- No results --}}
                        <div x-show="filteredCountries.length === 0" class="p-4 text-center text-sm text-base-content/60">
                            {{ __('No countries found') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phone Number Input --}}
            <div class="flex-1">
                <input
                    type="tel"
                    id="{{ $uuid }}"
                    wire:model.live.debounce.500ms="nationalNumber"
                    placeholder="{{ $placeholder ?? __('Phone number') }}"
                    class="input input-bordered w-full"
                    :class="{ 'input-error': !@js($this->isValid) && @entangle('nationalNumber').live }"
                    :required="@js($required)"
                    :disabled="@js($disabled)"
                >
            </div>
        </div>

        {{-- Hint or Error Message --}}
        @if($hint || !$this->isValid)
            <label class="label">
                @if(!$this->isValid && $nationalNumber)
                    <span class="label-text-alt text-error">
                        {{ __('Please enter a valid phone number') }}
                    </span>
                @elseif($hint)
                    <span class="label-text-alt">{{ $hint }}</span>
                @endif
            </label>
        @endif

        {{-- Display formatted number for debugging/preview --}}
        @if($this->formattedPhoneNumber)
            <label class="label">
                <span class="label-text-alt text-success">
                    {{ $this->formattedPhoneNumber }}
                </span>
            </label>
        @endif
    </div>
</div>
