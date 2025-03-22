@props([
    // Table configuration
    'columns' => [],
    'items' => [],
    'sortField' => null,
    'sortDirection' => 'asc',
    'sortAction' => 'sortBy',
    
    // Pagination configuration
    'hasMorePages' => false,
    'loadMoreAction' => 'loadMore',
    'totalCount' => 0,
    'loadedCount' => 0,
    'isLoading' => false,
    'infiniteScroll' => true,
    
    // Empty state
    'emptyMessage' => 'No items found',
    
    // Table appearance
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'bordered' => true,
    
    // Layout
    'withWrapper' => true,
    'withSearch' => false,
    'searchModel' => 'search',
    'searchPlaceholder' => 'Search...',
])

@php
    // Determine row classes based on striped and hover props
    $rowClasses = '';
    if ($striped) {
        $rowClasses .= ' even:bg-gray-50';
    }
    if ($hover) {
        $rowClasses .= ' hover:bg-gray-100';
    }
@endphp

<div {{ $attributes->merge(['class' => $withWrapper ? 'space-y-4' : '']) }}>
    <!-- Search and Filters (optional) -->
    @if($withSearch)
        <div class="mb-4">
            <x-filters.search-input 
                model="{{ $searchModel }}" 
                placeholder="{{ $searchPlaceholder }}" 
                class="max-w-md"
            />
        </div>
    @endif
    
    <!-- Headers/Controls Slot -->
    @if(isset($header))
        <div class="mb-4">
            {{ $header }}
        </div>
    @endif
    
    <!-- The Table -->
    <x-tables.sortable-table
        :columns="$columns"
        :sortField="$sortField"
        :sortDirection="$sortDirection"
        :sortAction="$sortAction"
        :responsive="$responsive"
        :striped="$striped"
        :hover="$hover"
        :bordered="$bordered"
    >
        @if(count($items) > 0)
            @foreach($items as $item)
                <tr class="{{ $rowClasses }}">
                    @foreach($columns as $column)
                        <td 
                            class="px-3 py-2 whitespace-nowrap text-sm 
                                {{ isset($column['align']) ? 'text-' . $column['align'] : 'text-left' }}
                                {{ isset($column['tdClass']) ? $column['tdClass'] : '' }}
                                {{ isset($column['hidden']) ? $column['hidden'] : '' }}"
                        >
                            @if(isset(${'cell_' . $column['field']}))
                                {{ ${'cell_' . $column['field']}($item) }}
                            @elseif(isset($column['format']))
                                {!! $column['format']($item) !!}
                            @else
                                {{ data_get($item, $column['field']) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="{{ count($columns) }}" class="px-3 py-8 text-center text-sm text-gray-500">
                    {{ $emptyMessage }}
                </td>
            </tr>
        @endif
    </x-tables.sortable-table>
    
    <!-- Pagination -->
    <x-tables.table-pagination
        :hasMorePages="$hasMorePages"
        :loadMoreAction="$loadMoreAction"
        :totalCount="$totalCount"
        :loadedCount="$loadedCount"
        :isLoading="$isLoading"
        :infiniteScroll="$infiniteScroll"
        class="mt-4"
    >
        @if(isset($pagination))
            {{ $pagination }}
        @endif
    </x-tables.table-pagination>
</div>