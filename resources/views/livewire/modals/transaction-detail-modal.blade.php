<div
    x-data="{}"
    x-init=""
    x-on:before-render="document.body.classList.remove('overflow-hidden');"
>
@if($show && $transaction)
<div
    x-data="{}"
    x-init="$nextTick(() => { document.body.classList.add('overflow-hidden'); })"
    x-on:keydown.escape.window="$wire.close()"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Header -->
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                        Transaction #{{ $transaction->tran_id }} Details
                    </h3>
                    <button 
                        wire:click="close" 
                        class="text-gray-400 hover:text-gray-500"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6">
                <!-- Transaction Summary -->
                <div class="mb-4 pb-4 border-b">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center">
                            <p class="text-sm text-gray-600 mr-2">Date: <span class="font-medium">{{ $transaction->date->format('M d, Y') }}</span></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-sm text-gray-600 mr-2">Type:</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $transaction->type === 'Invoice' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $transaction->type }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Customer: <span class="font-medium">{{ $transaction->customer_name }}</span></p>
                            @if($transaction->entity_id)
                                <p class="text-sm text-gray-600">Customer #: <span class="font-medium font-mono">{{ $transaction->entity_id }}</span></p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Items: <span class="font-medium">{{ $transaction->items->count() }}</span></p>
                            <p class="font-bold text-gray-900">Total: 
                                <span class="{{ $transaction->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format(abs($transaction->total_amount), 2) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Line Items -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Line Items</h4>
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block border rounded overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/6">SKU</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/2">Description</th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-1/6">Quantity</th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-1/6">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transaction->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-500 whitespace-nowrap overflow-hidden text-ellipsis">{{ $item->sku }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            <div class="truncate pr-4" title="{{ $item->item_description }}">{{ $item->item_description }}</div>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 text-right whitespace-nowrap">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-right whitespace-nowrap">
                                            <span class="{{ $item->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ${{ number_format(abs($item->amount), 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-900">Transaction Total:</td>
                                    <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">
                                        <span class="{{ $transaction->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format(abs($transaction->total_amount), 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Mobile List -->
                    <div class="md:hidden space-y-2">
                        @foreach($transaction->items as $item)
                            <div class="p-3 border rounded">
                                <div class="mb-1">
                                    <span class="text-sm font-medium block mb-1">{{ $item->item_description }}</span>
                                    <div class="flex justify-between border-t border-gray-100 pt-1">
                                        <div class="text-xs text-gray-500">
                                            <div>SKU: <span class="font-medium">{{ $item->sku }}</span></div>
                                            <div>Qty: <span class="font-medium">{{ number_format($item->quantity, 2) }}</span></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">Amount</div>
                                            <div class="text-sm font-bold {{ $item->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ${{ number_format(abs($item->amount), 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="p-3 border rounded bg-gray-50">
                            <div class="flex justify-between">
                                <span class="font-medium">Transaction Total:</span>
                                <span class="font-bold {{ $transaction->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format(abs($transaction->total_amount), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <a 
                    href="{{ route('sales.invoice', $transaction->id) }}"
                    target="_blank"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Download Invoice
                </a>
                
                <button 
                    wire:click="close" 
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>