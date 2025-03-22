@props([
    'columns' => [],
    'sortField' => null,
    'sortDirection' => 'asc',
    'sortAction' => 'sortBy',
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'bordered' => true,
])

@php
    // Determine table appearance classes based on props
    $tableClasses = 'min-w-full divide-y divide-gray-200';
    
    if ($bordered) {
        $tableClasses .= ' border border-gray-200';
    }
    
    // Determine row classes based on striped and hover props
    $rowClasses = '';
    if ($striped) {
        $rowClasses .= ' even:bg-gray-50';
    }
    if ($hover) {
        $rowClasses .= ' hover:bg-gray-100';
    }
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg ' . ($bordered ? 'border border-gray-200' : '')]) }}>
    @if($responsive)
        <div class="overflow-x-auto">
    @endif
    
    <table class="{{ $tableClasses }}">
        <thead class="bg-gray-50">
            <tr>
                @foreach($columns as $column)
                    <th 
                        scope="col" 
                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider
                               {{ $column['class'] ?? '' }}
                               {{ isset($column['hidden']) ? $column['hidden'] : '' }}"
                    >
                        @if(isset($column['sortable']) && $column['sortable'])
                            <button 
                                wire:click="{{ $sortAction }}('{{ $column['field'] }}')"
                                class="group inline-flex items-center space-x-1"
                            >
                                <span>{{ $column['label'] }}</span>
                                
                                <span class="relative flex items-center">
                                    @if($sortField === $column['field'])
                                        @if($sortDirection === 'asc')
                                            <svg class="w-3 h-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-3 h-3 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        @else
                            <span>{{ $column['label'] }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        
        <tbody class="bg-white divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
    
    @if($responsive)
        </div>
    @endif
</div>