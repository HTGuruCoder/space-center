@props(['wireModel' => 'pin', 'minLength' => 4, 'maxLength' => 6, 'label' => 'PIN Code'])

<div
    x-data="pinPad('{{ $wireModel }}', {{ $minLength }}, {{ $maxLength }})"
    x-init="init()"
    x-show="true"
    @keydown.window="$el.offsetParent !== null && handleKeyPress($event)"
    class="w-full max-w-sm mx-auto"
>
    {{-- Label --}}
    <label class="label">
        <span class="label-text font-semibold">{{ $label }}</span>
    </label>

    {{-- PIN Display --}}
    <div
        class="mb-6 p-6 bg-base-200 rounded-lg text-center transition-transform"
        :class="{ 'animate-shake': isShaking }"
    >
        <div class="text-4xl font-mono tracking-widest min-h-[3rem] flex items-center justify-center">
            <span x-text="displayPin || '••••'"></span>
        </div>
        <div class="text-xs text-base-content/60 mt-2">
            <span x-text="pin.length"></span> / <span x-text="{{ $maxLength }}"></span> {{ __('digits') }}
        </div>
    </div>

    {{-- Number Pad --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <template x-for="digit in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="digit">
            <button
                type="button"
                @click="addDigit(digit.toString())"
                :disabled="isMaxLength"
                class="btn btn-lg btn-outline"
                x-text="digit"
            ></button>
        </template>

        {{-- Delete Button --}}
        <button
            type="button"
            @click="deleteDigit()"
            :disabled="pin.length === 0"
            class="btn btn-lg btn-outline btn-error"
        >
            <x-icon name="mdi.backspace" class="w-6 h-6" />
        </button>

        {{-- Zero Button --}}
        <button
            type="button"
            @click="addDigit('0')"
            :disabled="isMaxLength"
            class="btn btn-lg btn-outline"
        >
            0
        </button>

        {{-- Clear Button --}}
        <button
            type="button"
            @click="clear()"
            :disabled="pin.length === 0"
            class="btn btn-lg btn-outline"
        >
            <x-icon name="mdi.close" class="w-6 h-6" />
        </button>
    </div>

    {{-- Validation Message --}}
    <div class="text-center text-sm">
        <span
            x-show="!isPinValid && pin.length > 0"
            class="text-error"
            x-cloak
        >
            {{ __('PIN must be between :min and :max digits', ['min' => $minLength, 'max' => $maxLength]) }}
        </span>
        <span
            x-show="isPinValid"
            class="text-success"
            x-cloak
        >
            ✓ {{ __('PIN is valid') }}
        </span>
    </div>
</div>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
    20%, 40%, 60%, 80% { transform: translateX(10px); }
}

.animate-shake {
    animation: shake 0.5s;
}
</style>
