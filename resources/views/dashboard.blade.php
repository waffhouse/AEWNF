<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div>
                                <h2 class="text-xl font-semibold">Dashboard</h2>
                                <p class="text-sm text-gray-600 mt-1">Welcome to your personal dashboard. Access key features below.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @php
                        // Define available dashboard cards based on permissions
                        $dashboardCards = [
                            'view catalog' => [
                                'title' => 'Product Catalog',
                                'description' => 'Browse our complete product catalog with pricing information.',
                                'route' => 'inventory.catalog',
                                'button_text' => 'View Catalog',
                                'color' => 'green'
                            ],
                            'view users' => [
                                'title' => 'Users List',
                                'description' => 'View all users in the system with their roles.',
                                'route' => 'staff.users_list',
                                'button_text' => 'View Users',
                                'color' => 'blue'
                            ],
                            'access admin dashboard' => [
                                'title' => 'Admin Dashboard',
                                'description' => 'Access the administration dashboard for the site.',
                                'route' => 'admin.dashboard',
                                'button_text' => 'Admin Dashboard',
                                'color' => 'red'
                            ],
                            // Add more permission-based cards here as needed
                        ];
                        @endphp

                        @foreach($dashboardCards as $permission => $card)
                            @can($permission)
                            <div class="bg-white p-5 rounded-lg border border-gray-200 shadow overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col h-full">
                                <div class="flex-grow">
                                    <h4 class="text-md font-medium text-gray-800 mb-2">{{ $card['title'] }}</h4>
                                    <p class="text-gray-600">{{ $card['description'] }}</p>
                                </div>
                                <div class="mt-6 pt-3">
                                    <a href="{{ route($card['route']) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-{{ $card['color'] }}-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-{{ $card['color'] }}-700 focus:bg-{{ $card['color'] }}-700 active:bg-{{ $card['color'] }}-900 focus:outline-none focus:ring-2 focus:ring-{{ $card['color'] }}-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ $card['button_text'] }}
                                    </a>
                                </div>
                            </div>
                            @endcan
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
