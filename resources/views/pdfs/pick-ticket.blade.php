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
    <div class="header clearfix">
        <div class="logo-container">
            <img src="{{ public_path('images/logo.png') }}" alt="A&E Wholesale" class="logo">
            <span style="vertical-align: middle; font-size: 16px; font-weight: bold; margin-left: 10px;">A&E Wholesale of North Florida</span>
        </div>
        <div class="title-container">
            <h1>Pick Ticket - Website Order #{{ $order->id }}</h1>
        </div>
    </div>
    
    <div class="info-section">
        <table class="order-info-table">
            <tr>
                <th colspan="6">ORDER INFORMATION</th>
            </tr>
            <tr>
                <td class="order-info-label" width="15%">Order #:</td>
                <td width="18%">{{ $order->id }}</td>
                <td class="order-info-label" width="15%">Status:</td>
                <td width="18%">
                    @if($order->status === \App\Models\Order::STATUS_PENDING)
                        Pending
                    @elseif($order->status === \App\Models\Order::STATUS_COMPLETED)
                        Completed
                    @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                        Cancelled
                    @endif
                </td>
                <td class="order-info-label" width="15%">Customer:</td>
                <td width="19%">{{ $order->user->name }}</td>
            </tr>
            <tr>
                <td class="order-info-label">Date:</td>
                <td>{{ $order->created_at->format('m/d/Y') }}</td>
                <td class="order-info-label">Type:</td>
                <td>
                    @if($order->delivery_type === \App\Models\Order::DELIVERY_TYPE_PICKUP)
                        Pickup
                    @elseif($order->delivery_type === \App\Models\Order::DELIVERY_TYPE_DELIVERY)
                        Delivery
                    @endif
                </td>
                <td class="order-info-label">
                    @if($order->user->customer_number)
                    Customer #:
                    @endif
                </td>
                <td>
                    @if($order->user->customer_number)
                        {{ $order->user->customer_number }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
    
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
                    <td class="quantity">{{ $item->quantity }}</td>
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
    
    <div class="signature-section">
        <table style="border: 1px solid #000; width: 100%;">
            <tr>
                <td style="width: 50%; border-left: none; border-top: none; border-bottom: none; padding: 6px 8px; height: 60px; vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 35px;">Picked By:</div>
                </td>
                <td style="width: 50%; border-right: none; border-top: none; border-bottom: none; padding: 6px 8px; height: 60px; vertical-align: top;">
                    <div style="font-weight: bold; margin-bottom: 35px;">Date:</div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        Generated by: {{ $generatedBy }} | {{ $generatedAt->format('m/d/Y h:i A') }} | A&E Wholesale of North Florida
    </div>
</body>
</html>