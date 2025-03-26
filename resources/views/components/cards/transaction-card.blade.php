@props(['sale'])

<div class="rounded-lg shadow overflow-hidden border border-gray-200 bg-white hover:shadow-md transition-shadow duration-300 h-full">
    <div class="p-4">
        <!-- Header with transaction type badge -->
        <div class="flex justify-between items-center mb-2">
            <span class="px-2 py-1 text-xs font-medium rounded 
                {{ $sale->type === 'Invoice' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $sale->type }}
            </span>
            <div class="text-sm text-gray-600">
                {{ $sale->date->format('M d, Y') }}
            </div>
        </div>
        
        <!-- Transaction ID -->
        <div class="mb-3">
            <h3 class="text-base font-bold text-gray-900 leading-tight">
                #{{ $sale->tran_id }}
            </h3>
        </div>
        
        <!-- Amount -->
        <div class="flex justify-between items-center border-t border-gray-100 pt-2">
            <span class="text-sm font-medium text-gray-600">Total:</span>
            <span class="text-lg font-bold {{ $sale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ${{ number_format(abs($sale->total_amount), 2) }}
            </span>
        </div>
        
        <!-- Action buttons -->
        <div class="flex justify-between mt-3 pt-2 border-t border-gray-100">
            <button 
                onclick="Livewire.dispatch('viewSaleDetails', { saleId: {{ $sale->id }} })"
                class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Details
            </button>
            
            <a 
                href="{{ route('sales.invoice', $sale->id) }}" 
                target="_blank"
                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Invoice
            </a>
        </div>
    </div>
</div>