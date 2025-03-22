@props([
    'placeholder' => 'Search...',
    'model' => null,
    'debounce' => '300ms',
    'submit' => false,
    'id' => null,
])

@php
    // Set default id if not provided
    $id = $id ?? 'search-input-' . Str::random(6);
    
    // Set appropriate wire:model directive based on submit preference
    $wireModel = $submit 
        ? "wire:model=\"{$model}\""
        : "wire:model.live.debounce.{$debounce}=\"{$model}\"";
    
    // Compute final classes with any additional classes passed via attributes
    $baseClass = 'w-full rounded-md focus:ring-blue-500 focus:border-blue-500';
    $rightButtonClass = $submit ? 'pl-3 pr-10' : 'pl-8 pr-3';
    $leftButtonClass = !$submit ? 'pl-8 pr-3' : 'pl-3 pr-10';
    $inputClass = $submit ? $rightButtonClass : $leftButtonClass;
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full']) }}>
    @if(!$submit)
        {{-- Left-aligned search icon (for non-submit version) --}}
        <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
        </div>
    @endif

    @if($submit)
        <form wire:submit.prevent class="w-full">
    @endif
    
    <input 
        id="{{ $id }}"
        type="search" 
        {!! $wireModel !!}
        placeholder="{{ $placeholder }}"
        class="{{ $baseClass }} {{ $inputClass }} border-gray-300 py-2 text-sm"
    >

    @if($submit)
            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-blue-500 hover:text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </form>
    @endif
    
    @if(isset($append))
        {{ $append }}
    @endif
</div>