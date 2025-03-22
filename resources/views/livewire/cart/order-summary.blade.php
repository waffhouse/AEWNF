<div class="bg-gray-50 p-6 rounded-lg border border-gray-200 sticky top-20">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
    
    <div class="border-t border-gray-200 pt-4">
        <div class="flex justify-between mb-2">
            <span class="text-sm text-gray-600">Items ({{ $itemCount }})</span>
            <span class="text-sm font-medium text-gray-900">${{ number_format($total, 2) }}</span>
        </div>
        
        <div class="mb-2 text-xs text-gray-500 italic">
            Tax exempt - Resale transactions
        </div>
        
        <div class="border-t border-gray-200 my-4"></div>
        
        <div class="flex justify-between mb-4">
            <span class="text-base font-medium text-gray-900">Total</span>
            <span class="text-base font-medium text-gray-900">${{ number_format($total, 2) }}</span>
        </div>
        
        @if($canPlaceOrders)
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes (Optional)</label>
                <textarea 
                    id="notes" 
                    wire:model="notes" 
                    rows="3" 
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Special instructions for your order"
                ></textarea>
            </div>
            
            <button 
                type="button"
                wire:click="checkout"
                wire:loading.attr="disabled"
                wire:confirm="Are you sure you want to place this order?"
                class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center justify-center"
            >
                <span wire:loading.remove wire:target="checkout">Place Order</span>
                <span wire:loading wire:target="checkout">Processing...</span>
            </button>
        @else
            <div class="text-center p-4 bg-yellow-50 rounded-md border border-yellow-200">
                <p class="text-sm text-yellow-800">
                    You do not have permission to place orders. Please contact support.
                </p>
            </div>
        @endif
    </div>
</div>