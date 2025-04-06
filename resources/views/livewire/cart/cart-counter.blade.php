<div wire:poll.30s class="cart-counter {{ $count > 0 ? '' : 'hidden' }}">
    @if($location == 'mobile-icon')
        <!-- Special styling for mobile icon in header -->
        <div class="flex flex-col items-end">
            <!-- Count Badge -->
            <span 
                class="{{ $count > 0 ? 'bg-red-600' : 'bg-gray-200' }} absolute -top-1 -right-1 px-1.5 py-0.5 text-xs font-medium rounded-full text-white"
                wire:key="cart-count-{{ $location }}"
                x-init="$nextTick(() => { $dispatch('cart-counter-updated', { count: {{ $count }}, total: {{ $total }} }) })"
            >
                <span class="count">{{ $count }}</span>
            </span>
            
            <!-- Total Display -->
            @if($showTotal && $count > 0)
                <span class="absolute top-6 right-0 whitespace-nowrap bg-white/95 text-gray-800 text-xs font-medium px-1.5 py-0.5 rounded border border-gray-200 shadow-sm">
                    ${{ number_format($total, 2) }}
                </span>
            @endif
        </div>
    @elseif($showTotal && $count > 0)
        <!-- Combined count and total for navigation links -->
        <div class="flex items-center ml-1.5">
            <span 
                class="{{ $count > 0 ? 'bg-red-600' : 'bg-gray-200' }} px-1.5 py-0.5 text-xs font-medium rounded-full text-white"
                wire:key="cart-count-{{ $location }}-2"
                x-init="$nextTick(() => { $dispatch('cart-counter-updated', { count: {{ $count }}, total: {{ $total }} }) })"
            >
                <span class="count">{{ $count }}</span>
            </span>
            <span class="ml-1.5 text-xs font-medium text-white">
                ${{ number_format($total, 2) }}
            </span>
        </div>
    @else
        <!-- Regular styling for desktop and mobile navigation (count only) -->
        <span 
            class="{{ $count > 0 ? 'bg-red-600' : 'bg-gray-200' }} ml-1.5 px-1.5 py-0.5 text-xs font-medium rounded-full text-white"
            wire:key="cart-count-{{ $location }}-3"
            x-init="$nextTick(() => { $dispatch('cart-counter-updated', { count: {{ $count }}, total: {{ $total }} }) })"
        >
            <span class="count">{{ $count }}</span>
        </span>
    @endif
</div>