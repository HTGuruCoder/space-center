@if($latitude && $longitude)
    <a
        href="https://www.google.com/maps/search/?api=1&query={{ $latitude }},{{ $longitude }}"
        target="_blank"
        class="link link-primary flex items-center gap-1"
    >
        <x-icon name="mdi.map-marker" class="w-4 h-4" />
        <span>{{ $latitude }}, {{ $longitude }}</span>
    </a>
@else
    <span class="text-base-content/50">-</span>
@endif
