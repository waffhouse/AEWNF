@props([
    'model' => null,
    'label' => null,
    'placeholder' => 'Select an option',
    'options' => [],
    'emptyOption' => true,
    'emptyOptionLabel' => 'All',
    'live' => true,
])

@php
    // Set appropriate wire:model directive based on live preference
    $wireModel = $live 
        ? "wire:model.live=\"{$model}\""
        : "wire:model=\"{$model}\"";
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    
    <div class="relative">
        <select 
            {!! $wireModel !!}
            class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full py-2 px-3 text-sm rounded-md appearance-none"
        >
            @if($emptyOption)
                <option value="">{{ $emptyOptionLabel }}</option>
            @endif
            
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</div>