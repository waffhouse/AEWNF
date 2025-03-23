<!-- Products list -->
<div>
    @if(empty($products))
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
            <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter options.</p>
            <div class="mt-6">
                <button wire:click="$dispatch('clear-all-filters')" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    @else
        <!-- Initial Loading Indicator is now shown in the sticky filter bar -->

        <!-- Spacer -->
        <div class="mb-4"></div>
        
        <!-- Card Grid View - 1 column on mobile, more on larger screens -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @foreach($products as $product)
                <div wire:key="product-{{ $product['id'] }}">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
        
        <!-- Infinite Scroll Controls -->
        <div class="mt-8 text-center" 
                x-data="{ 
                    observer: null,
                    observe() {
                        // Clean up any existing observer
                        if (this.observer) {
                            this.observer.disconnect();
                        }
                        
                        this.observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    @this.loadMore()
                                }
                            })
                        }, { rootMargin: '100px' })
                    
                        this.observer.observe(this.$el)
                    },
                    init() {
                        // Initial observation
                        this.observe();
                        
                        // Listen for navigation-related reinitialization
                        window.addEventListener('alpine-reinit', () => {
                            console.log('Alpine reinit detected, reinitializing product grid observer');
                            this.observe();
                        });
                        
                        // Clean up when component is destroyed
                        this.$cleanup = () => {
                            if (this.observer) {
                                this.observer.disconnect();
                                this.observer = null;
                            }
                        };
                    }
                }"
        >
            <!-- Loading indicator is now shown in the sticky filter bar -->

            <!-- End of Results Message -->
            <div x-show="!@js($hasMorePages)" class="py-4 text-sm text-gray-600">
                @if($totalCount === 0)
                    No products found
                @elseif($loadedCount === 1)
                    Showing 1 product
                @else
                    Showing all {{ $loadedCount }} products
                @endif
            </div>
            
            <!-- Extra space to ensure infinite scroll trigger is above sticky filter -->
            <div x-show="@js($hasMorePages)" class="h-24 mb-8"></div>
        </div>
    @endif
</div>