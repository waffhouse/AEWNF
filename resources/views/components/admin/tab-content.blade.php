@props(['id'])

<div x-show="activeTab === '{{ $id }}'" {{ $attributes }} style="display: none;">
    {{ $slot }}
</div>