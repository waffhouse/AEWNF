<div>
    <!-- User Form Modal -->
    <x-modals.form-modal
        name="user-form-modal"
        :title="$isEditMode ? 'Edit User' : 'Create New User'"
        :submit-method="$isEditMode ? 'updateUser' : 'createUser'"
        max-width="2xl"
    >
        <div class="grid grid-cols-1 gap-6">
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
            
            <!-- Customer selection comes first -->
            <div class="border rounded-lg p-5 bg-gray-50">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base font-medium text-gray-800">Select Customer</h3>
                    <span class="text-xs text-gray-600">(Will auto-assign the correct role)</span>
                </div>
                
                <!-- Search and filter tools -->
                <div class="flex flex-col sm:flex-row gap-3 mb-3">
                    <div class="flex-1">
                        <div class="relative">
                            <input 
                                wire:model.live.debounce.300ms="customer_search" 
                                type="text" 
                                placeholder="Search by ID or name..." 
                                class="w-full pl-10 pr-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="w-full sm:w-1/3">
                        <select 
                            wire:model.live="customer_state_filter"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                            <option value="">All States</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Customer selector -->
                <div class="border rounded-md shadow-sm max-h-64 overflow-y-auto bg-white">
                    @if(count($customers) > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($customers as $customer)
                                <li wire:key="customer-{{ $customer['id'] }}">
                                    <label 
                                        for="customer-{{ $customer['id'] }}"
                                        class="flex items-center px-4 py-3 cursor-pointer hover:bg-gray-50 
                                            {{ $selected_customer_id == $customer['id'] ? 'bg-indigo-50 border-l-4 border-indigo-500' : '' }}"
                                    >
                                        <input 
                                            type="radio" 
                                            wire:model.live="selected_customer_id" 
                                            id="customer-{{ $customer['id'] }}" 
                                            value="{{ $customer['id'] }}"
                                            class="form-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        >
                                        <div class="ml-3 flex-1">
                                            <div class="font-medium text-gray-900">
                                                {{ $customer['entity_id'] }} - {{ $customer['company_name'] }}
                                            </div>
                                            @if($customer['home_state'])
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $customer['home_state'] }}</div>
                                            @endif
                                        </div>
                                        @if($selected_customer_id == $customer['id'])
                                            <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-6 text-center text-gray-500">
                            No customers found matching your criteria. 
                            <button type="button" wire:click="$set('customer_search', '')" class="text-indigo-600 hover:underline focus:outline-none">
                                Clear search
                            </button>
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-between text-xs text-gray-500 mt-2 px-1">
                    <span>Select a customer to assign to this user</span>
                    <span>{{ count($customers) }} {{ Str::plural('customer', count($customers)) }} found</span>
                </div>
                @error('selected_customer_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                
                <!-- Hidden field to store the customer_number -->
                <input wire:model="customer_number" id="customer_number" type="hidden">
                @error('customer_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                
                <!-- Display selected customer information -->
                @if($customer_number && $selected_customer_id)
                    @php
                        $selectedCustomer = $customers->firstWhere('id', $selected_customer_id);
                    @endphp
                    @if($selectedCustomer)
                        <div class="mt-4 px-5 py-4 bg-white rounded-md text-sm border border-green-200 shadow-sm">
                            <div class="flex items-center border-b border-gray-100 pb-2 mb-2">
                                <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div class="font-medium text-gray-800">{{ $selectedCustomer['company_name'] }}</div>
                                @if($selectedCustomer['home_state'])
                                    <span class="ml-auto px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">
                                        {{ $selectedCustomer['home_state'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-gray-600">
                                    <span class="font-medium">Customer ID:</span> {{ $customer_number }}
                                </div>
                                <div class="text-gray-600">
                                    <span class="font-medium">Auto-assigned Role:</span> <span class="text-green-600 font-medium">{{ ucfirst($userRole) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
            
            <!-- Role selection moved after customer selection -->
            <div>
                <label for="userRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select wire:model.live="userRole" id="userRole" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    @if($selected_customer_id) disabled @endif
                >
                    <option value="">Select a role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @if($selected_customer_id)
                    <div class="text-xs text-blue-600 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Role auto-assigned based on customer's state
                    </div>
                @endif
                @error('userRole') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
    </x-modals.form-modal>
</div>