@props(['align' => 'start'])

<div  x-data="{ open: false }" @click.outside="open = false" style="position: relative; display: inline-block;">
    <div @click="open = !open" style="cursor: pointer;">
        {{ $trigger }}
    </div>

    <div class="card" x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        style="position: absolute; top: 100%; {{ $align === 'end' ? 'right: 0;' : 'left: 0;' }} z-index: 1050; margin-top: 4px; min-width: 12rem;"
        class="dropdown-menu-custom shadow bg-white rounded"
        >
        {{ $slot }}
    </div>
</div>