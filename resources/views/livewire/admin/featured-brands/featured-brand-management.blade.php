<div>
    <div class="px-4 py-5 sm:p-6 bg-white shadow rounded-lg">
        <div class="mb-5 flex justify-between items-center">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Featured Brands Management</h3>
            <button 
                type="button" 
                x-data
                @click="$dispatch('open-modal', 'add-brand-modal'); $wire.openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
            >
                Add Brand
            </button>
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
        
        <div class="mt-6 mb-8">
            <p class="text-sm text-gray-600">
                Featured brands will appear on the user dashboard in the order specified below. 
                Toggle the status to show or hide a brand without removing it from the list.
            </p>
        </div>
        
        <!-- Desktop View: Table for medium screens and up -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
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
                                {{ $brand->brand }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button" wire:click="toggleActive({{ $brand->id }})" class="flex items-center">
                                    <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $brand->creator ? $brand->creator->name : 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $brand->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button 
                                        type="button" 
                                        x-data
                                        @click="$dispatch('open-modal', 'edit-brand-modal'); $wire.openEditModal({{ $brand->id }})"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </button>
                                    <button 
                                        type="button" 
                                        x-data
                                        @click="$dispatch('open-modal', 'delete-brand-modal'); $wire.confirmDelete({{ $brand->id }})"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
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
                        <div class="font-medium text-gray-900">{{ $brand->brand }}</div>
                        <button type="button" wire:click="toggleActive({{ $brand->id }})" class="flex items-center">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $brand->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </button>
                    </div>
                    
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Display Order</span>
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
                    </div>
                    
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Created By</span>
                        <span class="text-sm text-gray-900">{{ $brand->creator ? $brand->creator->name : 'Unknown' }}</span>
                    </div>
                    
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                        <span class="text-sm text-gray-500">Created On</span>
                        <span class="text-sm text-gray-900">{{ $brand->created_at->format('M d, Y') }}</span>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            x-data
                            @click="$dispatch('open-modal', 'edit-brand-modal'); $wire.openEditModal({{ $brand->id }})"
                            class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
                        >
                            Edit
                        </button>
                        <button 
                            type="button" 
                            x-data
                            @click="$dispatch('open-modal', 'delete-brand-modal'); $wire.confirmDelete({{ $brand->id }})"
                            class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                        >
                            Delete
                        </button>
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
    
    <!-- Add Brand Modal -->
    <x-modal name="add-brand-modal" :show="$showAddBrandModal" maxWidth="md" x-on:close="$wire.set('showAddBrandModal', false)">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Add Featured Brand</h2>
            
            <div class="mb-4">
                <label for="brandToAdd" class="block text-sm font-medium text-gray-700">Select Brand</label>
                <select
                    id="brandToAdd"
                    wire:model="brandToAdd"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                >
                    <option value="">-- Select a brand --</option>
                    @foreach($availableBrands as $brandName)
                        <option value="{{ $brandName }}">{{ $brandName }}</option>
                    @endforeach
                </select>
                @error('brandToAdd') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="mb-4">
                <label for="displayOrder" class="block text-sm font-medium text-gray-700">Display Order</label>
                <input
                    type="number"
                    id="displayOrder"
                    wire:model="displayOrder"
                    min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                >
                @error('displayOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="cancelAdd"
                    x-data
                    @click="$dispatch('close-modal', 'add-brand-modal')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="addBrand"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Add
                </button>
            </div>
        </div>
    </x-modal>
    
    <!-- Edit Brand Modal -->
    <x-modal name="edit-brand-modal" :show="$showEditBrandModal" maxWidth="md" x-on:close="$wire.set('showEditBrandModal', false)">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Featured Brand</h2>
            
            <div class="mb-4">
                <label for="editBrandName" class="block text-sm font-medium text-gray-700">Brand Name</label>
                <select
                    id="editBrandName"
                    wire:model="brandToAdd"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                >
                    <option value="">-- Select a brand --</option>
                    @foreach($availableBrands as $brandName)
                        <option value="{{ $brandName }}">{{ $brandName }}</option>
                    @endforeach
                </select>
                @error('brandToAdd') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="mb-4">
                <label for="editDisplayOrder" class="block text-sm font-medium text-gray-700">Display Order</label>
                <input
                    type="number"
                    id="editDisplayOrder"
                    wire:model="displayOrder"
                    min="1"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                >
                @error('displayOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="cancelEdit"
                    x-data
                    @click="$dispatch('close-modal', 'edit-brand-modal')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="updateBrand"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Update
                </button>
            </div>
        </div>
    </x-modal>
    
    <!-- Delete Confirmation Modal -->
    <x-modal name="delete-brand-modal" :show="$showDeleteModal" maxWidth="md" x-on:close="$wire.set('showDeleteModal', false)">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h2>
            
            <p class="mb-4 text-sm text-gray-600">
                Are you sure you want to delete this featured brand? This action cannot be undone.
                <br><br>
                <strong>Brand:</strong> {{ isset($currentBrand) && $currentBrand ? $currentBrand->brand : '' }}
            </p>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="cancelDelete"
                    x-data
                    @click="$dispatch('close-modal', 'delete-brand-modal')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="deleteBrand"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Delete
                </button>
            </div>
        </div>
    </x-modal>
</div>