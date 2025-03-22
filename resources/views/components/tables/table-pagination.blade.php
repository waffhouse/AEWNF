@props([
    'hasMorePages' => false,
    'loadMoreAction' => 'loadMore',
    'totalCount' => 0,
    'loadedCount' => 0,
    'isLoading' => false,
    'infiniteScroll' => true,
    'paginationInfo' => true,
])

<div {{ $attributes }}>
    @if($infiniteScroll)
        <div 
            x-data="{ 
                observer: null,
                init() {
                    this.setupObserver();
                    
                    // Re-initialize observer when data changes
                    $wire.on('$refresh', () => {
                        setTimeout(() => this.setupObserver(), 50);
                    });
                },
                setupObserver() {
                    if (this.observer) {
                        this.observer.disconnect();
                    }
                    
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                @this.{{ $loadMoreAction }}();
                            }
                        });
                    }, { rootMargin: '100px' });
                    
                    this.observer.observe(this.$el);
                },
                destroy() {
                    if (this.observer) {
                        this.observer.disconnect();
                    }
                }
            }"
            class="w-full text-center py-4"
        >
            <!-- Loading Indicator -->
            <div wire:loading.block wire:target="{{ $loadMoreAction }}" class="py-4">
                <svg class="animate-spin h-6 w-6 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Loading more items...</span>
            </div>

            <!-- Manual Load More Button (when not automatically loading) -->
            @if(!$hasMorePages && $paginationInfo)
                <div class="py-2 text-sm text-gray-600">
                    @if($totalCount === 0)
                        No items found
                    @elseif($loadedCount === 1)
                        Showing 1 item
                    @else
                        Showing all {{ $loadedCount }} items{{ $totalCount > $loadedCount ? " of {$totalCount}" : "" }}
                    @endif
                </div>
            @elseif(!$isLoading && !$infiniteScroll)
                <button 
                    wire:click="{{ $loadMoreAction }}"
                    class="px-4 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-150"
                >
                    Load more
                </button>
            @endif
        </div>
    @else
        {{ $slot }}
    @endif
</div>