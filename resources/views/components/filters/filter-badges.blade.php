@props([
    'filters' => [],
    'resetAllEvent' => 'resetAllFilters',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2 items-center']) }}>
    @if(count($filters) > 0)
        <span class="text-sm font-medium text-gray-600">Active filters:</span>
        
        <div class="flex flex-wrap gap-2">
            @foreach($filters as $filter)
                @if($filter['active'])
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $filter['label'] }}: {{ $filter['value'] }}
                        
                        @if($filter['removable'] ?? true)
                            <button 
                                wire:click="{{ $filter['removeEvent'] }}"
                                type="button" 
                                class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-600 focus:outline-none focus:bg-blue-500 focus:text-white"
                            >
                                <span class="sr-only">Remove {{ $filter['label'] }} filter</span>
                                <svg class="h-2.5 w-2.5" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                    <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                </svg>
                            </button>
                        @endif
                    </span>
                @endif
            @endforeach
            
            <button 
                wire:click="{{ $resetAllEvent }}"
                type="button" 
                class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Clear all
            </button>
        </div>
    @endif
</div>