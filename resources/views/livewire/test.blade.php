<div>
    <div class="container mx-auto p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Livewire Test Component</h1>
            <p class="text-base-content/60">Test and develop UI components here</p>
        </div>

        <div class="divider"></div>

        {{-- Phone Number Input Test --}}
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title">Phone Number Input Test</h2>

                <form wire:submit="save">
                    <livewire:ui.forms.phone-number-input
                        wire:model="phoneNumber"
                        label="Phone Number"
                        hint="Enter your phone number with country code"
                        placeholder="555-1234"
                        :required="true"
                    />

                    <div class="mt-4">
                        <button type="submit" class="btn bg-primary">
                            Save Phone Number
                        </button>
                    </div>
                </form>

                @if($phoneNumber)
                    <div class="mt-4 p-4 bg-base-200 rounded-lg">
                        <p class="text-sm font-semibold mb-2">Current Value (E.164 format):</p>
                        <code class="text-sm">{{ $phoneNumber }}</code>
                    </div>
                @endif
            </div>
        </div>

        {{-- Mary UI Components Test --}}
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title">Mary UI Components</h2>

                <div class="space-y-4">
                    <x-input
                        label="Text Input"
                        wire:model="testInput"
                        placeholder="Type something..."
                        hint="This is a Mary UI input"
                    />

                    <x-select
                        label="Country"
                        :options="App\Enums\CountryEnum::options()"
                        placeholder="Select a country"
                    />

                    <x-select
                        label="Currency"
                        :options="App\Enums\CurrencyEnum::options()"
                        placeholder="Select a currency"
                    />
                </div>
            </div>
        </div>

        {{-- DaisyUI Components Test --}}
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title">DaisyUI Button Variants</h2>
                <div class="flex flex-wrap gap-2">
                    <button class="btn">Default</button>
                    <button class="btn btn-primary">Primary</button>
                    <button class="btn btn-secondary">Secondary</button>
                    <button class="btn btn-accent">Accent</button>
                    <button class="btn btn-ghost">Ghost</button>
                    <button class="btn btn-link">Link</button>
                    <button class="btn btn-info">Info</button>
                    <button class="btn btn-success">Success</button>
                    <button class="btn btn-warning">Warning</button>
                    <button class="btn btn-error">Error</button>
                </div>
            </div>
        </div>

        {{-- Alert Examples --}}
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title">Alert Variants</h2>
                <div class="space-y-2">
                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Info alert!</span>
                    </div>

                    <div class="alert alert-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Success alert!</span>
                    </div>

                    <div class="alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Warning alert!</span>
                    </div>

                    <div class="alert alert-error">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Error alert!</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
