@props([
    'name',
    'title',
    'show' => false,
    'maxWidth' => '4xl',
    'closeLabel' => 'Close',
    'hidePrimaryAction' => false,
    'primaryLabel' => 'Action',
    'primaryMethod' => null,
    'primaryParams' => null,
    'primaryAction' => null,
    'primaryColor' => 'blue',
    'isLoading' => false
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    '6xl' => 'sm:max-w-6xl',
    '7xl' => 'sm:max-w-7xl',
][$maxWidth];

$primaryClasses = [
    'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'indigo' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500', 
    'yellow' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
][$primaryColor] ?? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        @click.outside="$dispatch('close-modal', '{{ $name }}')"
    >
        <!-- Header -->
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6 sm:flex sm:justify-between sm:items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ $title }}
            </h3>
            
            <button 
                type="button"
                class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
            >
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Modal Content Area -->
        <div class="px-4 py-5 sm:p-6 bg-white overflow-y-auto max-h-[calc(100vh-16rem)]">
            {{ $slot }}
        </div>
        
        <!-- Actions Footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            @if(!$hidePrimaryAction)
                @if($primaryMethod)
                <button 
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white {{ $primaryClasses }} focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                    wire:click="{{ $primaryMethod }}{{ $primaryParams ? '(' . $primaryParams . ')' : '' }}"
                    @if($isLoading)
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    @endif
                >
                    @if($isLoading)
                        <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    @endif
                    {{ $primaryLabel }}
                </button>
                @else
                <button 
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white {{ $primaryClasses }} focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                    x-on:click="{{ $primaryAction ?? 'show = false' }}"
                >
                    {{ $primaryLabel }}
                </button>
                @endif
            @endif

            <!-- Custom footer actions slot -->
            @if(isset($actions))
                <div class="sm:flex-1 sm:flex">{{ $actions }}</div>
            @endif
            
            <button
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 {{ !$hidePrimaryAction ? 'sm:ml-3' : '' }} sm:w-auto sm:text-sm"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
                @if($isLoading)
                    wire:loading.attr="disabled"
                @endif
            >
                {{ $closeLabel }}
            </button>
        </div>
    </div>
</div>