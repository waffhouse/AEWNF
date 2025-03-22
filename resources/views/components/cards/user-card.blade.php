@props([
    'user' => null,
    'showRoles' => true,
    'showEmail' => true,
    'showCustomerNumber' => true,
    'showControls' => true,
    'editAction' => 'openUserEdit',
    'deleteAction' => 'confirmDeleteUser',
    'deleteConfirmText' => 'Are you sure you want to delete this user? This action cannot be undone.',
])

@php
    $hasRoles = isset($user->roles) && $user->roles->count() > 0;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300']) }}>
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <h3 class="text-md font-semibold text-gray-900 truncate">
                {{ $user->name ?? 'Unknown User' }}
            </h3>
            
            <!-- Email -->
            @if($showEmail && isset($user->email))
                <p class="mt-1 text-sm text-gray-500 truncate">
                    {{ $user->email }}
                </p>
            @endif
            
            <!-- Customer Number -->
            @if($showCustomerNumber && isset($user->customer_number) && $user->customer_number)
                <p class="mt-1 text-sm text-gray-500">
                    <span class="font-medium">Customer #:</span>
                    <span class="font-mono">{{ $user->customer_number }}</span>
                </p>
            @endif
        </div>
        
        <!-- Controls - Edit/Delete buttons -->
        @if($showControls)
            <div class="flex-shrink-0 flex">
                @can('edit users')
                    <button 
                        wire:click="{{ $editAction }}({{ $user->id }})" 
                        class="text-blue-600 hover:text-blue-900 mr-2"
                        title="Edit User"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                @endcan

                @can('delete users')
                    <button 
                        wire:click="{{ $deleteAction }}({{ $user->id }})" 
                        wire:confirm="{{ $deleteConfirmText }}"
                        class="text-red-600 hover:text-red-900"
                        title="Delete User"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endcan
            </div>
        @endif
    </div>
    
    <!-- User roles -->
    @if($showRoles && $hasRoles)
        <div class="mt-3">
            <div class="flex flex-wrap gap-1">
                @foreach($user->roles as $role)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        @if($role->name === 'admin') bg-red-100 text-red-800
                        @elseif($role->name === 'staff') bg-blue-100 text-blue-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ ucfirst($role->name) }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Custom slot for additional content -->
    @if(isset($footer))
        <div class="mt-3 pt-3 border-t border-gray-100">
            {{ $footer }}
        </div>
    @endif
</div>