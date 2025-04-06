<div>
    <div class="px-4 py-5 sm:p-6 bg-white shadow rounded-lg">
        <div class="mb-5 flex justify-between items-center">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Featured Brands Management</h3>
        </div>
        
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        
        <div class="mt-6 mb-8 flex justify-between items-start">
            <p class="text-sm text-gray-600">
                Featured brands will appear on the user dashboard in the order specified below. 
                Toggle the status to show or hide a brand without removing it from the list.
            </p>
            
            <button 
                type="button" 
                wire:click="showAddForm"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md border border-transparent"
                @if($showAddBrandForm) disabled @endif
            >
                Add Brand
            </button>
        </div>
        
        @if($showAddBrandForm)
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-md font-medium text-gray-900 mb-3">Add New Featured Brand</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="newBrandName" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select
                        id="newBrandName"
                        wire:model="newBrandName"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                    >
                        <option value="">Select a brand</option>
                        @foreach($availableBrands as $brandName)
                            <option value="{{ $brandName }}">{{ $brandName }}</option>
                        @endforeach
                    </select>
                    @error('newBrandName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="newDisplayOrder" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input
                        type="number"
                        id="newDisplayOrder"
                        wire:model="newDisplayOrder"
                        min="1"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                    >
                    @error('newDisplayOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="newIsActive" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        id="newIsActive"
                        wire:model="newIsActive"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                    >
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    @error('newIsActive') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="mt-4 flex justify-end space-x-2">
                <button 
                    type="button" 
                    wire:click="addBrand"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md border border-transparent"
                >
                    Save Brand
                </button>
                <button 
                    type="button" 
                    wire:click="cancelAdd"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-md border border-gray-300"
                >
                    Cancel
                </button>
            </div>
        </div>
        @endif
        
        <!-- Desktop View: Table for medium screens and up -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Order</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created On</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($featuredBrands as $brand)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1">
                                    <button type="button" wire:click="moveUp({{ $brand->id }})" class="text-gray-400 hover:text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <span class="text-sm text-gray-900">{{ $brand->display_order }}</span>
                                    <button type="button" wire:click="moveDown({{ $brand->id }})" class="text-gray-400 hover:text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                @if ($editingBrandId === $brand->id)
                                    <div class="flex items-center space-x-2">
                                        <select
                                            wire:model="editBrandName"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                        >
                                            <option value="">Select a brand</option>
                                            @foreach($availableBrands as $brandName)
                                                <option value="{{ $brandName }}">{{ $brandName }}</option>
                                            @endforeach
                                        </select>
                                        @error('editBrandName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    {{ $brand->brand }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($editingBrandId === $brand->id)
                                    <select 
                                        wire:model.live="editIsActive" 
                                        class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                    >
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                @else
                                    <button type="button" wire:click="toggleActive({{ $brand->id }})" class="flex items-center">
                                        <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $brand->creator ? $brand->creator->name : 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $brand->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @if ($editingBrandId === $brand->id)
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                type="button" 
                                                wire:click="saveEdit"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Save
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="cancelEdit"
                                                class="text-gray-600 hover:text-gray-900"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    @elseif ($brandToDelete === $brand->id)
                                        <div class="flex items-center space-x-2 bg-red-50 p-1 rounded">
                                            <span class="text-xs text-red-700">Confirm delete?</span>
                                            <button wire:click="deleteBrand" class="text-red-700 hover:text-red-900 text-xs font-bold">
                                                Yes
                                            </button>
                                            <button wire:click="cancelDelete" class="text-gray-600 hover:text-gray-900 text-xs">
                                                No
                                            </button>
                                        </div>
                                    @else
                                        <button 
                                            type="button" 
                                            wire:click="startEdit({{ $brand->id }})"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="confirmDelete({{ $brand->id }})"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No featured brands found. Add brands to display on the dashboard.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile View: Card layout for small screens -->
        <div class="md:hidden space-y-4">
            @forelse($featuredBrands as $brand)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        @if ($editingBrandId === $brand->id)
                            <select
                                wire:model="editBrandName"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                            >
                                <option value="">Select a brand</option>
                                @foreach($availableBrands as $brandName)
                                    <option value="{{ $brandName }}">{{ $brandName }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="font-medium text-gray-900">{{ $brand->brand }}</div>
                            <button type="button" wire:click="toggleActive({{ $brand->id }})" class="flex items-center">
                                <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </button>
                        @endif
                    </div>
                    
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Display Order</span>
                        @if ($editingBrandId === $brand->id)
                            <input
                                type="number"
                                wire:model="editDisplayOrder"
                                min="1"
                                class="block w-16 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                            >
                        @else
                            <div class="flex items-center space-x-1">
                                <button type="button" wire:click="moveUp({{ $brand->id }})" class="text-gray-400 hover:text-gray-600 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="text-sm font-medium text-gray-900">{{ $brand->display_order }}</span>
                                <button type="button" wire:click="moveDown({{ $brand->id }})" class="text-gray-400 hover:text-gray-600 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    @if ($editingBrandId === $brand->id)
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Status</span>
                        <select 
                            wire:model.live="editIsActive" 
                            class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        >
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    @endif
                    
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Created By</span>
                        <span class="text-sm text-gray-900">{{ $brand->creator ? $brand->creator->name : 'Unknown' }}</span>
                    </div>
                    
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Created On</span>
                        <span class="text-sm text-gray-900">{{ $brand->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-50 flex justify-end">
                        @if ($editingBrandId === $brand->id)
                            <div class="flex space-x-2">
                                <button 
                                    wire:click="saveEdit" 
                                    class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-md hover:bg-green-200"
                                >
                                    Save
                                </button>
                                <button 
                                    wire:click="cancelEdit" 
                                    class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Cancel
                                </button>
                            </div>
                        @elseif ($brandToDelete === $brand->id)
                            <div class="flex items-center space-x-2 bg-red-50 p-1 rounded">
                                <span class="text-xs text-red-700">Confirm delete?</span>
                                <button wire:click="deleteBrand" class="text-red-700 hover:text-red-900 text-xs font-bold">
                                    Yes
                                </button>
                                <button wire:click="cancelDelete" class="text-gray-600 hover:text-gray-900 text-xs">
                                    No
                                </button>
                            </div>
                        @else
                            <div class="flex space-x-2">
                                <button 
                                    type="button" 
                                    wire:click="startEdit({{ $brand->id }})"
                                    class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
                                >
                                    Edit
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="confirmDelete({{ $brand->id }})"
                                    class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                >
                                    Delete
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500">
                    No featured brands found. Add brands to display on the dashboard.
                </div>
            @endforelse
        </div>
        
        <div class="mt-4">
            {{ $featuredBrands->links() }}
        </div>
    </div>
</div>