@php
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

$currentLocale = LaravelLocalization::getCurrentLocale();
$supportedLocales = LaravelLocalization::getSupportedLocales();
@endphp

<div class="dropdown dropdown-end">
    <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
        <x-icon name="mdi.translate" class="w-5 h-5" />
        <span class="hidden sm:inline">{{ strtoupper($currentLocale) }}</span>
        <x-icon name="mdi.chevron-down" class="w-4 h-4" />
    </label>

    <ul tabindex="0" class="dropdown-content menu menu-sm bg-base-100 rounded-box shadow-lg w-52 p-2 mt-2 z-50">
        @foreach($supportedLocales as $localeCode => $properties)
            <li>
                <a
                    href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                    @class([
                        'flex items-center gap-2',
                        'active' => $currentLocale === $localeCode
                    ])
                >
                    {{-- Flag emoji --}}
                    <span class="text-lg">
                        @if($localeCode === 'en')
                            🇺🇸
                        @elseif($localeCode === 'es')
                            🇪🇸
                        @elseif($localeCode === 'fr')
                            🇫🇷
                        @endif
                    </span>

                    {{-- Language name --}}
                    <span>{{ $properties['native'] }}</span>

                    {{-- Checkmark if active --}}
                    @if($currentLocale === $localeCode)
                        <x-icon name="mdi.check" class="w-4 h-4 ml-auto text-primary" />
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
