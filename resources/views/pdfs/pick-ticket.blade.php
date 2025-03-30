<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order #{{ $order->id }} - Pick Ticket</title>
    <style>
        body, td, th, div, p, span {
            font-family: Arial, sans-serif;
        }
        body {
            font-size: 10px;
            line-height: 1.3;
            margin: 10px;
        }
        .header {
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            height: 40px;
        }
        .logo-container {
            float: left;
            height: 40px;
            line-height: 40px;
            white-space: nowrap;
        }
        .logo {
            max-height: 30px;
            vertical-align: middle;
            display: inline-block;
        }
        .title-container {
            float: right;
            height: 40px;
            line-height: 40px;
        }
        h1 {
            font-size: 16px;
            margin: 0;
            color: #111827;
            display: inline;
            vertical-align: middle;
        }
        .info-section {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000;
        }
        table th {
            text-align: left;
            padding: 6px 4px;
            border: 1px solid #000;
            font-size: 12px;
            text-transform: uppercase;
            color: #000;
            font-weight: bold;
            background-color: #e5e7eb;
        }
        table td {
            padding: 6px 4px;
            border: 1px solid #000;
            vertical-align: middle;
            font-size: 12px;
        }
        /* Fix for consistent border appearance */
        table thead tr:first-child th {
            border-top: none;
        }
        table thead tr:first-child th:first-child {
            border-left: none;
        }
        table thead tr:first-child th:last-child {
            border-right: none;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        table tbody tr td:first-child {
            border-left: none;
        }
        table tbody tr td:last-child {
            border-right: none;
        }
        .quantity {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #6B7280;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #ccc;
        }
        .page-break {
            page-break-after: always;
        }
        .check-box {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
            margin: 0 auto;
        }
        .signature-section {
            margin-top: 15px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 70%;
            display: inline-block;
            margin-top: 15px;
        }
        .category-totals {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .category-totals th {
            text-align: left;
            padding: 3px 4px;
            border: 1px solid #000;
            font-size: 9px;
            text-transform: uppercase;
        }
        .category-totals td {
            padding: 3px 4px;
            border: 1px solid #000;
            font-size: 9px;
        }
        .category-totals .quantity {
            text-align: center;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        .category-column {
            float: left;
            border-right: 1px dotted #ccc;
            padding-right: 10px;
            margin-right: 10px;
            box-sizing: border-box;
        }
        .category-column:last-child {
            border-right: none;
            padding-right: 0;
            margin-right: 0;
        }
        .category-item {
            margin-bottom: 2px;
            border-bottom: 1px dotted #ccc;
            overflow: hidden;
        }
        .category-name {
            font-weight: bold;
            float: left;
        }
        .category-quantity {
            float: right;
        }
        .order-info-table {
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .order-info-table th {
            font-size: 13px;
            text-align: left;
            background-color: #e5e7eb;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
            border-left: none;
            border-right: none;
            border-top: none;
        }
        .order-info-table td {
            border: none;
            border-bottom: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 12px;
        }
        .order-info-table tr:last-child td {
            border-bottom: none;
        }
        .order-info-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div style="margin-bottom: 15px; padding-bottom: 10px;">
        <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 0;">
            <tr>
                <td style="vertical-align: middle; width: 60%; border: none; padding: 0;">
                    <div style="display: flex; align-items: center;">
                        <img src="{{ public_path('images/logo.png') }}" alt="A&E Wholesale" style="max-height: 40px; vertical-align: middle;">
                        <span style="vertical-align: middle; font-size: 16px; font-weight: bold; margin-left: 10px;">A&E Wholesale of North Florida</span>
                    </div>
                </td>
                <td style="vertical-align: middle; text-align: right; width: 40%; border: none; padding: 0;">
                    <div style="font-size: 16px; font-weight: bold; color: #111827;">
                        Pick Ticket - Website Order #{{ $order->id }}
                    </div>
                    <div style="font-size: 12px; color: #4B5563; margin-top: 2px;">
                        Date: {{ $order->created_at->format('m/d/Y') }} | 
                        @if($order->delivery_type === \App\Models\Order::DELIVERY_TYPE_PICKUP)
                            Pickup
                        @elseif($order->delivery_type === \App\Models\Order::DELIVERY_TYPE_DELIVERY)
                            Delivery
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Customer Information Section -->
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 10px;">
        <tr>
            <th colspan="4" style="font-size: 11px; text-align: left; background-color: #e5e7eb; border-bottom: 1px solid #000; padding: 3px 5px; font-weight: bold;">
                CUSTOMER INFORMATION
            </th>
        </tr>
        <tr>
            <td width="15%" style="padding: 4px; font-weight: bold; font-size: 10px;">Customer:</td>
            <td width="35%" style="padding: 4px; font-size: 10px;">
                @if($order->user->customer && $order->user->customer->company_name)
                    {{ $order->user->customer->company_name }}
                @else
                    {{ $order->user->name }}
                @endif
            </td>
            <td width="15%" style="padding: 4px; font-weight: bold; font-size: 10px;">Customer #:</td>
            <td width="35%" style="padding: 4px; font-size: 10px;">{{ $order->user->customer_number ?: 'N/A' }}</td>
        </tr>
        
        @if($order->user->customer)
        <tr>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">Phone:</td>
            <td style="padding: 4px; font-size: 10px;">{{ $order->user->customer->phone ?: 'N/A' }}</td>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">Terms:</td>
            <td style="padding: 4px; font-size: 10px;">{{ $order->user->customer->terms ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">State:</td>
            <td style="padding: 4px; font-size: 10px;">{{ $order->user->customer->home_state ?: 'N/A' }}</td>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">County:</td>
            <td style="padding: 4px; font-size: 10px;">{{ $order->user->customer->county ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">License:</td>
            <td style="padding: 4px; font-size: 10px;">
                @if($order->user->customer->license_number)
                    @if($order->user->customer->license_type)
                        {{ $order->user->customer->license_type }}
                    @else
                        License #
                    @endif
                @else
                    N/A
                @endif
            </td>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">License #:</td>
            <td style="padding: 4px; font-size: 10px;">{{ $order->user->customer->license_number ?: 'N/A' }}</td>
        </tr>
        
        @if($order->user->customer->shipping_address)
        <tr>
            <td style="padding: 4px; font-weight: bold; font-size: 10px;">Address:</td>
            <td colspan="3" style="padding: 4px; font-size: 10px;">{{ $order->user->customer->shipping_address }}</td>
        </tr>
        @endif
        @endif
    </table>
    
    @if($order->notes)
    <div class="info-section">
        <h2>Order Notes</h2>
        <p>{{ $order->notes }}</p>
    </div>
    @endif
    
    <div class="info-section">
        <table>
            <thead>
                <tr>
                    <th colspan="5" style="text-align: center; font-size: 18px; padding: 8px 4px;">
                        ITEMS TO PICK: <span style="font-weight: bold;">{{ $order->getTotalItems() }}</span>
                    </th>
                </tr>
                <tr>
                    <th width="10%">Picked</th>
                    <th width="10%" style="text-align: center">QTY</th>
                    <th width="10%">SKU</th>
                    <th width="50%">Product</th>
                    <th width="20%" style="text-align: center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="text-align: center; font-family: Arial, sans-serif;">
                        <div class="check-box"></div>
                    </td>
                    <td class="quantity">{{ floor($item->quantity) == $item->quantity ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->product_sku }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td style="text-align: center; font-family: Arial, sans-serif; font-size: 10px;">
                        @if(!$item->inventory || $item->inventory->quantity <= 0)
                            <span style="font-weight: bold; text-transform: uppercase; color: #B91C1C; font-family: Arial, sans-serif;">OUT OF STOCK</span>
                        @else
                            <span style="font-weight: bold; text-transform: uppercase; color: #065F46; font-family: Arial, sans-serif;">IN STOCK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
    </div>
    
    <div class="info-section">
        <table class="category-totals-table" style="border: 1px solid #000; width: 100%;">
            <tr>
                <th colspan="6" style="border-left: none; border-right: none; border-top: none;">CATEGORY TOTALS</th>
            </tr>
            <tr>
                <td style="padding: 0; border: none;" colspan="6">
                    @php
                        $categorizedItems = $order->getItemsByCategory();
                        $totalCategories = count($categorizedItems);
                        $columnCount = min(3, max(1, ceil($totalCategories / 5))); // 1-5 items: 1 col, 6-10: 2 cols, 11+: 3 cols
                        $categoriesArray = [];
                        
                        foreach ($categorizedItems as $category => $data) {
                            $categoriesArray[] = [
                                'name' => $category,
                                'quantity' => $data['total_quantity']
                            ];
                        }
                        
                        $itemsPerColumn = ceil(count($categoriesArray) / $columnCount);
                    @endphp
                    
                    <table style="width: 100%; border: none; margin: 0;">
                        <tr>
                            @for($col = 0; $col < $columnCount; $col++)
                                <td style="width: {{ 100/$columnCount }}%; vertical-align: top; padding: 8px; border-right: {{ $col < $columnCount-1 ? '1px dotted #ccc' : 'none' }};">
                                    <ul style="list-style-type: none; margin: 0; padding: 0;">
                                    @for($i = $col * $itemsPerColumn; $i < min(($col + 1) * $itemsPerColumn, count($categoriesArray)); $i++)
                                        @if($i < count($categoriesArray))
                                            <li style="margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <span style="font-weight: bold; display: inline-block; min-width: 20px; margin-right: 6px; text-align: right;">{{ $categoriesArray[$i]['quantity'] }}</span>
                                                <span style="display: inline-block;">{{ $categoriesArray[$i]['name'] }}</span>
                                            </li>
                                        @endif
                                    @endfor
                                    </ul>
                                </td>
                            @endfor
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="border-top: 1px solid #000; background-color: #f3f4f6;">
                <td colspan="6" style="padding: 8px; border: none; font-weight: bold; font-size: 13px;">
                    <div style="overflow: hidden;">
                        <span style="float: left; text-transform: uppercase;">TOTAL ITEMS</span>
                        <span style="float: right; font-size: 13px;">{{ $order->getTotalItems() }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="margin-top: 20px; margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 5px;">Picked By:</div>
        <div style="border-bottom: 1px solid #000; width: 100%; height: 30px;"></div>
    </div>
    
    <div class="footer">
        Generated by: {{ $generatedBy }} | {{ $generatedAt->format('m/d/Y h:i A') }} | A&E Wholesale of North Florida
    </div>
</body>
</html>