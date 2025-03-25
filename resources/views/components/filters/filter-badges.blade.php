@props([
    'filters' => [],
    'resetAllEvent' => 'resetAllFilters',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2 items-center']) }}>
    @if(count($filters) > 0)
        <span class="text-sm font-medium text-gray-700">Active Filters:</span>
        
        <div class="flex flex-wrap gap-2">
            @foreach($filters as $filter)
                @if($filter['active'])
                    @php
                        // Determine color based on filter label
                        $bgColor = match($filter['label']) {
                            'Search' => 'bg-blue-100 hover:bg-blue-200 text-blue-800',
                            'Role' => 'bg-green-100 hover:bg-green-200 text-green-800',
                            default => 'bg-purple-100 hover:bg-purple-200 text-purple-800'
                        };
                        
                        $iconColor = match($filter['label']) {
                            'Search' => 'text-blue-500',
                            'Role' => 'text-green-500',
                            default => 'text-purple-500'
                        };
                    @endphp
                    
                    <button 
                        type="button" 
                        wire:click="{{ $filter['removeEvent'] }}"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $bgColor }} transition-colors focus:outline-none"
                        aria-label="Remove {{ $filter['label'] }} filter"
                    >
                        {{ $filter['label'] }}: {{ $filter['value'] }}
                        <span class="ml-1 {{ $iconColor }}">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </button>
                @endif
            @endforeach
            
            @if(count($filters) > 1)
                <button 
                    wire:click="{{ $resetAllEvent }}"
                    type="button" 
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors focus:outline-none"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear all
                </button>
            @endif
        </div>
    @endif
</div>