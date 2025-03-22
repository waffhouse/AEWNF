<div>
    <!-- User Form Modal -->
    <x-modals.form-modal
        name="user-form-modal"
        :title="$isEditMode ? 'Edit User' : 'Create New User'"
        :submit-method="$isEditMode ? 'updateUser' : 'createUser'"
    >
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label for="user-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input wire:model="name" id="user-name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" id="email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Password {{ $isEditMode ? '(Leave blank to keep current)' : '' }}
                </label>
                <div class="relative">
                    <input 
                        wire:model="password" 
                        id="password" 
                        :type="showPassword ? 'text' : 'password'" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pr-10"
                    >
                    <button 
                        type="button"
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                    >
                        <svg 
                            x-show="!showPassword" 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-5 w-5" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg 
                            x-show="showPassword" 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-5 w-5" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label for="userRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select wire:model.live="userRole" id="userRole" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Select a role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @error('userRole') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <!-- Customer number field, conditionally shown based on role -->
            <div x-data="{}" x-show="['customer', 'florida customer', 'georgia customer'].includes($wire.userRole)" x-transition>
                <label for="customer_number" class="block text-sm font-medium text-gray-700 mb-1">Customer Number (4 digits)</label>
                <input wire:model="customer_number" id="customer_number" type="text" maxlength="4" placeholder="e.g. 1234" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <div class="text-xs text-gray-500 mt-1">Unique identifier for customer (must be 4 digits and unique)</div>
                @error('customer_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
        </div>
    </x-modals.form-modal>
</div>