<div>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Hero Banner -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 sm:rounded-lg shadow-md mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                    <h2 class="text-2xl font-bold">{{ $viewMode === 'brands' ? 'Brand' : 'Category' }} Analytics</h2>
                    <p class="text-sm text-red-100 mt-1">Interactive dashboard showing {{ $viewMode === 'brands' ? 'brand' : 'category' }} performance over time.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('sales.analytics') }}" class="inline-flex items-center px-4 py-2 bg-white text-red-600 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                        </svg>
                        Back to Analytics
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- View Mode Switch -->
                    <div class="md:col-span-3 mb-4">
                        <div class="flex items-center justify-center">
                            <span class="mr-3 text-sm font-medium text-gray-700">View Mode:</span>
                            <div class="flex justify-center">
                                <div class="inline-flex rounded-md shadow-sm" role="group">
                                    <button
                                        type="button"
                                        wire:click="$set('viewMode', 'brands')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-l-lg {{ $viewMode === 'brands' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                                    >
                                        Brands
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="$set('viewMode', 'classes')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-r-lg {{ $viewMode === 'classes' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                                    >
                                        Categories
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="date" 
                                        id="startDate"
                                        wire:model.live="startDate" 
                                        class="border-gray-300 focus:ring-red-500 focus:border-red-500 block w-full pl-10 pr-3 py-2 text-sm rounded-md"
                                        placeholder="Start Date"
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input 
                                        type="date" 
                                        id="endDate"
                                        wire:model.live="endDate" 
                                        class="border-gray-300 focus:ring-red-500 focus:border-red-500 block w-full pl-10 pr-3 py-2 text-sm rounded-md"
                                        placeholder="End Date"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" id="isAllTime" wire:model.live="isAllTime" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                            <label for="isAllTime" class="ml-2 text-sm text-gray-600">Show all time data</label>
                        </div>
                    </div>

                    <!-- Brands or Classes Selection with Interactive Tags -->
                    <div class="md:col-span-2">
                        @if($viewMode === 'brands')
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Brands (Optional)</label>
                            
                            <!-- Brand Search Input -->
                            <div x-data="{ brandSearch: '', showDropdown: false, brands: {{ json_encode($availableBrands) }} }">
                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="brandSearch"
                                        @focus="showDropdown = true"
                                        @click.away="showDropdown = false"
                                        placeholder="Search for brands..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    >
                                    
                                    <!-- Dropdown for Brand Selection -->
                                    <div 
                                        x-show="showDropdown" 
                                        x-transition
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md max-h-60 overflow-y-auto"
                                    >
                                        <ul class="py-1">
                                            <template x-for="brand in brands.filter(b => b.toLowerCase().includes(brandSearch.toLowerCase()))" :key="brand">
                                                <li 
                                                    x-text="brand"
                                                    @click="$wire.addBrand(brand); showDropdown = false; brandSearch = '';"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                ></li>
                                            </template>
                                            <li x-show="brands.filter(b => b.toLowerCase().includes(brandSearch.toLowerCase())).length === 0" class="px-4 py-2 text-gray-500">
                                                No matching brands
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Selected Brand Tags -->
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($selectedBrands as $index => $brand)
                                        <div class="inline-flex items-center px-2 py-1 rounded-md text-sm" 
                                             style="background-color: hsla({{ ($index * 137) % 360 }}, 70%, 50%, 0.1); 
                                                    color: hsl({{ ($index * 137) % 360 }}, 70%, 35%);
                                                    border: 1px solid hsl({{ ($index * 137) % 360 }}, 70%, 50%);">
                                            {{ $brand }}
                                            <button type="button" wire:click="removeBrand('{{ $brand }}')" class="ml-1 hover:opacity-80">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    @if(empty($selectedBrands))
                                        <div class="text-sm text-gray-500">No brands selected (showing top {{ $displayLimit }})</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Categories (Optional)</label>
                            
                            <!-- Class Search Input -->
                            <div x-data="{ 
                                classSearch: '', 
                                showDropdown: false, 
                                classes: {{ json_encode($availableClasses) }},
                                init() {
                                    console.log('Alpine initialized for classes', this.classes);
                                    
                                    // Fix any null values in the classes array to prevent errors
                                    if (this.classes) {
                                        this.classes = this.classes.filter(c => c != null);
                                    }
                                }
                            }">
                                <!-- Debug info - will be removed later -->
                                <div class="mb-2 text-xs text-gray-500">{{ count($availableClasses) }} categories found</div>
                                
                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="classSearch"
                                        @focus="showDropdown = true"
                                        @click.away="showDropdown = false"
                                        placeholder="Search for categories..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    >
                                    
                                    <!-- Dropdown for Class Selection -->
                                    <div 
                                        x-show="showDropdown" 
                                        x-transition
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md max-h-60 overflow-y-auto"
                                    >
                                        <ul class="py-1">
                                            <template x-for="className in classes.filter(c => c.toLowerCase().includes(classSearch.toLowerCase()))" :key="className">
                                                <li 
                                                    x-text="className"
                                                    @click="$wire.addClass(className); showDropdown = false; classSearch = '';"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                ></li>
                                            </template>
                                            <li x-show="classes.filter(c => c.toLowerCase().includes(classSearch.toLowerCase())).length === 0" class="px-4 py-2 text-gray-500">
                                                No matching categories
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Selected Class Tags -->
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($selectedClasses as $index => $class)
                                        <div class="inline-flex items-center px-2 py-1 rounded-md text-sm" 
                                             style="background-color: hsla({{ ($index * 137) % 360 }}, 70%, 50%, 0.1); 
                                                    color: hsl({{ ($index * 137) % 360 }}, 70%, 35%);
                                                    border: 1px solid hsl({{ ($index * 137) % 360 }}, 70%, 50%);">
                                            {{ $class }}
                                            <button type="button" wire:click="removeClass('{{ $class }}')" class="ml-1 hover:opacity-80">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    @if(empty($selectedClasses))
                                        <div class="text-sm text-gray-500">No categories selected (showing top {{ $displayLimit }})</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Performance Over Time Chart -->
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $viewMode === 'brands' ? 'Brand' : 'Category' }} Sales Over Time</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($isAllTime)
                            All Time
                        @else
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div class="p-4">
                    <div class="h-80">
                        <canvas id="topBrandsOverTimeChart"></canvas>
                    </div>
                    <!-- Hidden data elements for chart -->
                    <div class="hidden">
                        <div id="top-brands-over-time-data">
                            {{ $viewMode === 'brands' 
                                ? json_encode($chartData['brands']['timeSeriesData'] ?? []) 
                                : json_encode($chartData['classes']['timeSeriesData'] ?? []) 
                            }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Chart -->
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $viewMode === 'brands' ? 'Brand' : 'Category' }} Comparison</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($isAllTime)
                            All Time
                        @else
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div class="p-4">
                    <div class="h-80">
                        <canvas id="brandComparisonChart"></canvas>
                    </div>
                    <!-- Hidden data elements for chart -->
                    <div class="hidden">
                        <div id="brand-comparison-data">
                            {{ $viewMode === 'brands' 
                                ? json_encode($chartData['brands']['comparisonData'] ?? []) 
                                : json_encode($chartData['classes']['comparisonData'] ?? []) 
                            }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Chart.js directly in the component -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Initialize charts on component load and update
        document.addEventListener('DOMContentLoaded', function() {
            window.initBrandCharts();
        });
        
        document.addEventListener('livewire:load', function() {
            window.initBrandCharts();
        });
        
        document.addEventListener('livewire:update', function() {
            // Try multiple times with increasing delays
            setTimeout(() => window.initBrandCharts(), 50);
            setTimeout(() => window.initBrandCharts(), 150);
            setTimeout(() => window.initBrandCharts(), 300);
        });
        
        // Listen for our custom event using Livewire event system
        document.addEventListener('DOMContentLoaded', function() {
            if (window.Livewire) {
                window.Livewire.on('brandClassDataUpdated', function() {
                    console.log('Brand/Class data updated Livewire event received');
                    setTimeout(() => window.initBrandCharts(), 10);
                });
                
                window.Livewire.on('viewModeChanged', function(data) {
                    console.log('View mode changed to:', data.mode);
                    setTimeout(() => window.initBrandCharts(), 10);
                });
            }
        });
        
        // Global state to track chart instances
        if (!window.brandAnalyticsState) {
            window.brandAnalyticsState = {
                topBrandsOverTimeChart: null,
                brandComparisonChart: null
            };
        }
        
        // Global function to initialize charts
        window.initBrandCharts = function() {
            // Clean up existing charts first
            if (window.brandAnalyticsState.topBrandsOverTimeChart) {
                window.brandAnalyticsState.topBrandsOverTimeChart.destroy();
                window.brandAnalyticsState.topBrandsOverTimeChart = null;
            }
            
            if (window.brandAnalyticsState.brandComparisonChart) {
                window.brandAnalyticsState.brandComparisonChart.destroy();
                window.brandAnalyticsState.brandComparisonChart = null;
            }
            
            // Get chart elements
            const timeSeriesCanvas = document.getElementById('topBrandsOverTimeChart');
            const comparisonCanvas = document.getElementById('brandComparisonChart');
            const timeSeriesData = document.getElementById('top-brands-over-time-data');
            const comparisonData = document.getElementById('brand-comparison-data');
            
            if (!timeSeriesCanvas || !comparisonCanvas || !timeSeriesData || !comparisonData) {
                return;
            }
            
            try {
                // Time series chart
                try {
                    const chartData = JSON.parse(timeSeriesData.textContent);
                    if (chartData && chartData.labels && chartData.datasets) {
                        // Generate colors for datasets
                        chartData.datasets = chartData.datasets.map((dataset, index) => {
                            const hue = (index * 137) % 360;
                            return {
                                ...dataset,
                                borderColor: `hsl(${hue}, 70%, 50%)`,
                                backgroundColor: `hsla(${hue}, 70%, 50%, 0.1)`,
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true
                            };
                        });
                        
                        // Create chart and store in global state
                        window.brandAnalyticsState.topBrandsOverTimeChart = new Chart(timeSeriesCanvas, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: chartData.datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Sales Amount ($)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Time Period'
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `${context.dataset.label}: $${context.raw.toLocaleString()}`;
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'top'
                                    }
                                }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Error creating time series chart:', err);
                }
                
                // Comparison chart
                try {
                    const chartData = JSON.parse(comparisonData.textContent);
                    if (chartData && chartData.labels && chartData.datasets) {
                        // Set colors
                        if (chartData.datasets[0]) {
                            chartData.datasets[0].backgroundColor = 'rgba(220, 38, 38, 0.7)';
                            chartData.datasets[0].borderColor = 'rgb(185, 28, 28)';
                            chartData.datasets[0].borderWidth = 1;
                        }
                        
                        if (chartData.datasets[1]) {
                            chartData.datasets[1].borderColor = 'rgb(37, 99, 235)';
                            chartData.datasets[1].backgroundColor = 'rgba(37, 99, 235, 0.1)';
                            chartData.datasets[1].borderWidth = 2;
                            chartData.datasets[1].tension = 0.4;
                            chartData.datasets[1].pointBackgroundColor = 'rgb(37, 99, 235)';
                            chartData.datasets[1].pointRadius = 4;
                        }
                        
                        // Create chart and store in global state
                        window.brandAnalyticsState.brandComparisonChart = new Chart(comparisonCanvas, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: chartData.datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        type: 'linear',
                                        position: 'left',
                                        title: {
                                            display: true,
                                            text: 'Sales Amount ($)'
                                        }
                                    },
                                    y1: {
                                        beginAtZero: true,
                                        type: 'linear',
                                        position: 'right',
                                        grid: {
                                            drawOnChartArea: false
                                        },
                                        title: {
                                            display: true,
                                            text: 'Units Sold'
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const datasetLabel = context.dataset.label;
                                                const value = context.raw;
                                                if (datasetLabel.includes('Amount')) {
                                                    return `${datasetLabel}: $${value.toLocaleString()}`;
                                                } else {
                                                    return `${datasetLabel}: ${value.toLocaleString()}`;
                                                }
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'top'
                                    }
                                }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Error creating comparison chart:', err);
                }
            } catch (e) {
                console.error('Error initializing charts:', e);
            }
        };
    </script>
</div>