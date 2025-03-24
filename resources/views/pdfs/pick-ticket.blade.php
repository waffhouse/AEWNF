<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order #{{ $order->id }} - Pick Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            margin: 0;
            color: #111827;
        }
        .subtitle {
            font-size: 16px;
            margin: 5px 0 0;
            color: #6B7280;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h2 {
            font-size: 14px;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
            color: #111827;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 3px 0;
        }
        .info-label {
            font-weight: bold;
            width: 30%;
        }
        .info-value {
            width: 70%;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pickup {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .badge-delivery {
            background-color: #EDE9FE;
            color: #6D28D9;
        }
        .badge-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .badge-completed {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .badge-cancelled {
            background-color: #FEE2E2;
            color: #B91C1C;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            text-align: left;
            padding: 8px;
            background-color: #F3F4F6;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
            text-transform: uppercase;
            color: #4B5563;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
            vertical-align: top;
        }
        .quantity {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #6B7280;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }
        .page-break {
            page-break-after: always;
        }
        .check-box {
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 5px;
        }
        .signature-section {
            margin-top: 30px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 70%;
            display: inline-block;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/AEWBlack.png') }}" alt="A&E Wholesale" class="logo">
        <h1>Pick Ticket</h1>
        <p class="subtitle">Order #{{ $order->id }}</p>
    </div>
    
    <div class="info-section">
        <div style="width: 48%; float: left;">
            <h2>Order Information</h2>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell info-label">Order Number:</div>
                    <div class="info-cell info-value">#{{ $order->id }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Date:</div>
                    <div class="info-cell info-value">{{ $order->created_at->format('m/d/Y h:i A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Status:</div>
                    <div class="info-cell info-value">
                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                            <span class="badge badge-pending">Pending</span>
                        @elseif($order->status === \App\Models\Order::STATUS_COMPLETED)
                            <span class="badge badge-completed">Completed</span>
                        @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                            <span class="badge badge-cancelled">Cancelled</span>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Delivery Type:</div>
                    <div class="info-cell info-value">
                        @if($order->delivery_type === \App\Models\Order::DELIVERY_TYPE_PICKUP)
                            <span class="badge badge-pickup">Pickup</span>
                        @elseif($order->delivery_type === \App\Models\Order::DELIVERY_TYPE_DELIVERY)
                            <span class="badge badge-delivery">Delivery</span>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Total Items:</div>
                    <div class="info-cell info-value">{{ $order->getTotalItems() }}</div>
                </div>
            </div>
        </div>
        
        <div style="width: 48%; float: right;">
            <h2>Customer Information</h2>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell info-label">Name:</div>
                    <div class="info-cell info-value">{{ $order->user->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Email:</div>
                    <div class="info-cell info-value">{{ $order->user->email }}</div>
                </div>
                @if($order->user->customer_number)
                <div class="info-row">
                    <div class="info-cell info-label">Customer #:</div>
                    <div class="info-cell info-value">{{ $order->user->customer_number }}</div>
                </div>
                @endif
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    @if($order->notes)
    <div class="info-section">
        <h2>Order Notes</h2>
        <p>{{ $order->notes }}</p>
    </div>
    @endif
    
    <div class="info-section">
        <h2>Items to Pick</h2>
        <table>
            <thead>
                <tr>
                    <th width="10%">Picked</th>
                    <th width="10%">Quantity</th>
                    <th width="15%">SKU</th>
                    <th width="55%">Product</th>
                    <th width="10%">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="text-align: center">
                        <div class="check-box"></div>
                    </td>
                    <td class="quantity">{{ $item->quantity }}</td>
                    <td>{{ $item->product_sku }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="text-align: right; margin-top: 10px; font-weight: bold;">
            Order Total: ${{ number_format($order->total, 2) }}
        </div>
    </div>
    
    <div class="signature-section">
        <div style="width: 48%; float: left;">
            <p>Picked By:</p>
            <div class="signature-line"></div>
        </div>
        
        <div style="width: 48%; float: right;">
            <p>Date:</p>
            <div class="signature-line"></div>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <div class="footer">
        <p>
            Generated by: {{ $generatedBy }} | 
            Generated on: {{ $generatedAt->format('m/d/Y h:i A') }} | 
            A&E Wholesale of North Florida
        </p>
    </div>
</body>
</html>