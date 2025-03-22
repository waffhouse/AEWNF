@props([
    'startDate' => null,
    'endDate' => null,
    'startLabel' => 'From',
    'endLabel' => 'To',
    'placeholder' => 'Select date',
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row gap-2 ' . $class]) }}>
    <div class="w-full sm:w-1/2">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $startLabel }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <input 
                type="date" 
                wire:model="{{ $startDate }}" 
                class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 text-sm rounded-md"
                placeholder="{{ $placeholder }}"
            >
        </div>
    </div>
    
    <div class="w-full sm:w-1/2">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $endLabel }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <input 
                type="date" 
                wire:model="{{ $endDate }}" 
                class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 text-sm rounded-md"
                placeholder="{{ $placeholder }}"
            >
        </div>
    </div>
</div>