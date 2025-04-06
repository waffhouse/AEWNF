@props(['id'])

<div 
    x-show="activeTab === '{{ $id }}'" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    {{ $attributes }} 
    style="display: none;"
    wire:key="{{ $id }}-tab-panel"
>
    {{ $slot }}
</div>