<div>
    <!-- Permission Form Modal -->
    <x-modals.form-modal
        name="permission-form-modal"
        :title="$editId ? 'Edit Permission' : 'Create New Permission'"
        :submit-method="$editId ? 'updatePermission' : 'createPermission'"
        :submit-label="$editId ? 'Update Permission' : 'Create Permission'"
        max-width="lg"
    >
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label for="permission-name" class="block text-sm font-medium text-gray-700 mb-1">Permission Name</label>
                <input wire:model="name" id="permission-name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Roles</label>
                <div class="bg-white p-3 border border-gray-300 rounded-md max-h-48 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($availableRoles as $role)
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="roles" value="{{ $role->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('roles') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
    </x-modals.form-modal>
</div>