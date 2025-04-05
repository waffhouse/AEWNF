<div>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Hero Banner with Red Gradient Background -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 sm:rounded-lg shadow-md mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div>
                    <h2 class="text-2xl font-bold">Sales Analytics</h2>
                    <p class="text-sm text-red-100 mt-1">Access advanced analytics and reports for sales performance.</p>
                </div>
            </div>
        </div>

        <!-- Analytics Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Sales History Card -->
            <a href="{{ route('sales') }}" class="bg-gradient-to-br from-blue-50 to-white border border-blue-200 rounded-lg shadow-sm overflow-hidden transition-all hover:shadow-md">
                <div class="p-4 border-b border-blue-100">
                    <h3 class="text-base font-semibold text-blue-900">Sales History</h3>
                </div>
                <div class="p-5 flex items-start">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-2 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-600">
                            View your complete transaction history with detailed information on invoices and credit memos.
                        </p>
                    </div>
                </div>
            </a>
            
            <!-- Customers Without Sales Card -->
            <a href="{{ route('sales.customers-without-sales') }}" class="bg-gradient-to-br from-purple-50 to-white border border-purple-200 rounded-lg shadow-sm overflow-hidden transition-all hover:shadow-md">
                <div class="p-4 border-b border-purple-100">
                    <h3 class="text-base font-semibold text-purple-900">Customers Without Sales</h3>
                </div>
                <div class="p-5 flex items-start">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-2 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-600">
                            Identify customers who haven't made any purchases to target for marketing and outreach.
                        </p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Future Reports Placeholder -->
        <div class="bg-white shadow-sm rounded-lg mb-6 border border-gray-200">
            <div class="p-6 text-center">
                <div class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">More Analytics Coming Soon</h3>
                <p class="text-gray-600 max-w-lg mx-auto">
                    We're currently developing advanced analytics components including:
                </p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg mx-auto text-left">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Sales by Category Analysis</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <a href="{{ route('sales.top-brands') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Brand Performance Metrics</a>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Seasonal Trend Reports</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Customer Segment Analysis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>