@props(['tabs', 'counts', 'icons' => [
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
    'roles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />',
    'permissions' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />',
    'tag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />',
    'shopping-bag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />',
]])

<div class="border-b border-gray-200">
    <!-- Responsive tabs that wrap on small screens -->
    <nav class="-mb-px flex flex-wrap gap-2 sm:gap-6" aria-label="Tabs">
        @foreach($tabs as $id => $tab)
            @php
                $colorClass = match($tab['color'] ?? 'gray') {
                    'blue' => 'border-blue-500 text-blue-600',
                    'green' => 'border-green-500 text-green-600',
                    'purple' => 'border-purple-500 text-purple-600',
                    'amber' => 'border-amber-500 text-amber-600',
                    'red' => 'border-red-500 text-red-600',
                    default => 'border-gray-500 text-gray-600'
                };
                
                $inactiveClass = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                $badgeClass = match($tab['color'] ?? 'gray') {
                    'blue' => 'bg-blue-100 text-blue-800',
                    'green' => 'bg-green-100 text-green-800',
                    'purple' => 'bg-purple-100 text-purple-800',
                    'amber' => 'bg-amber-100 text-amber-800',
                    'red' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                
                $icon = $icons[$tab['icon'] ?? 'document'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />';
            @endphp
            
            <button 
                @click="activeTab = '{{ $id }}'; $wire.setActiveTab('{{ $id }}'); $wire.$refresh();"
                :class="{ '{{ $colorClass }}': activeTab === '{{ $id }}', '{{ $inactiveClass }}': activeTab !== '{{ $id }}' }"
                class="py-2 px-1 border-b-2 font-medium text-xs sm:text-sm flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $icon !!}
                </svg>
                <span>{{ $tab['name'] }}</span>
                @if(isset($counts[$id]) && $counts[$id] !== null)
                    <span class="ml-1 px-1.5 py-0.5 {{ $badgeClass }} text-xs rounded-md">{{ $counts[$id] }}</span>
                @endif
            </button>
        @endforeach
    </nav>
</div>