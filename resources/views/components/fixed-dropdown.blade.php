@props([
    'position' => 'top-left', // top-left, top-right, bottom-left, bottom-right
])

<div x-data="{
    open: false,
    position: '{{ $position }}',
    updatePosition() {
        this.$nextTick(() => {
            const button = this.$refs.trigger;
            const menu = this.$refs.menu;
            if (button && menu) {
                const rect = button.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.zIndex = '9999';

                // Calculate position based on prop
                if (this.position === 'top-left') {
                    menu.style.top = (rect.top - menu.offsetHeight - 8) + 'px';
                    menu.style.left = rect.left + 'px';
                } else if (this.position === 'top-right') {
                    menu.style.top = (rect.top - menu.offsetHeight - 8) + 'px';
                    menu.style.left = (rect.right - menu.offsetWidth) + 'px';
                } else if (this.position === 'bottom-left') {
                    menu.style.top = (rect.bottom + 8) + 'px';
                    menu.style.left = rect.left + 'px';
                } else if (this.position === 'bottom-right') {
                    menu.style.top = (rect.bottom + 8) + 'px';
                    menu.style.left = (rect.right - menu.offsetWidth) + 'px';
                }
            }
        });
    }
}" @click.away="open = false" {{ $attributes->class(['relative']) }}>
    <div @click="open = !open; updatePosition()" x-ref="trigger">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-ref="menu"
         x-transition
         @click="open = false"
         class="w-fit bg-base-100 rounded-lg shadow-lg border border-base-300"
         style="display: none;">
        <ul class="menu p-2">
            {{ $slot }}
        </ul>
    </div>
</div>
