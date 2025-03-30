<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $sale->tran_id }}</title>
    <style>
        @page {
            margin: 48px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1F2937;
            margin: 0;
            padding: 0;
        }
        .header {
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .logo-container {
            float: left;
        }
        .logo {
            max-width: 250px;
            max-height: 80px;
            display: block;
        }
        .invoice-details {
            float: right;
            text-align: right;
            padding-top: 10px;
        }
        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .document-number {
            font-size: 18px;
            color: #111827;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .document-date {
            font-size: 14px;
            color: #6B7280;
        }
        .bill-to-ship-to {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #F3F4F6;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 12px;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 12px;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 0;
        }
        .totals-table .total-row td {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #E5E7EB;
            padding-top: 12px;
        }
        .totals-table .label {
            color: #6B7280;
        }
        .totals-table .amount {
            text-align: right;
            color: #111827;
        }
        .thank-you {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #EF4444;
            margin: 40px 0;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="text-align: left; vertical-align: top;">
                    <img src="{{ public_path('images/AEWBlack.png') }}" alt="A&E Wholesale of North Florida" class="logo">
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div class="document-title">
                        {{ $sale->type }} #{{ $sale->tran_id }}
                    </div>
                    <div class="document-date">
                        Date: {{ $sale->date->format('F d, Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table width="100%" style="margin-bottom: 30px; border: 1px solid #E5E7EB;">
        <tr>
            <th colspan="4" style="font-size: 14px; text-align: left; background-color: #F3F4F6; border-bottom: 1px solid #E5E7EB; padding: 8px 10px; font-weight: bold;">
                CUSTOMER INFORMATION
            </th>
        </tr>
        <tr>
            <td width="15%" style="padding: 6px 10px; font-weight: bold; color: #6B7280; vertical-align: top; border-right: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; background-color: #F9FAFB;">Customer:</td>
            <td width="85%" colspan="3" style="padding: 6px 10px; border-bottom: 1px solid #E5E7EB;">
                <div style="font-weight: bold; font-size: 13px;">{{ $sale->customer_name }}</div>
                <div>ID: {{ $sale->entity_id }}</div>
            </td>
        </tr>

        @if(isset($customer))
        <tr>
            <td style="padding: 6px 10px; font-weight: bold; color: #6B7280; vertical-align: top; border-right: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; background-color: #F9FAFB;">Contact:</td>
            <td style="padding: 6px 10px; border-bottom: 1px solid #E5E7EB;">
                @if($customer->phone)
                    <div>Phone: {{ $customer->phone }}</div>
                @else
                    <div>No phone on file</div>
                @endif
            </td>
            <td style="padding: 6px 10px; font-weight: bold; color: #6B7280; vertical-align: top; border-left: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; background-color: #F9FAFB;">Terms:</td>
            <td style="padding: 6px 10px; border-bottom: 1px solid #E5E7EB;">
                {{ $customer->terms ?: 'N/A' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 6px 10px; font-weight: bold; color: #6B7280; vertical-align: top; border-right: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; background-color: #F9FAFB;">Location:</td>
            <td style="padding: 6px 10px; border-bottom: 1px solid #E5E7EB;">
                <div>County: {{ $customer->county ?: 'N/A' }}</div>
            </td>
            <td style="padding: 6px 10px; font-weight: bold; color: #6B7280; vertical-align: top; border-left: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; background-color: #F9FAFB;">License:</td>
            <td style="padding: 6px 10px; border-bottom: 1px solid #E5E7EB;">
                @if($customer->license_number)
                    @if($customer->license_type)
                        <div>{{ $customer->license_type }} {{ $customer->license_number }}</div>
                    @else
                        <div>License # {{ $customer->license_number }}</div>
                    @endif
                @else
                    <div>N/A</div>
                @endif
            </td>
        </tr>
        @if($customer->shipping_address)
        <tr>
            <td style="padding: 6px 10px; font-weight: bold; color: #6B7280; vertical-align: top; border-right: 1px solid #E5E7EB; background-color: #F9FAFB;">Address:</td>
            <td colspan="3" style="padding: 6px 10px;">{{ $customer->shipping_address }}</td>
        </tr>
        @endif
        @endif
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%" class="text-center">Quantity</th>
                <th width="15%">SKU</th>
                <th width="45%">Description</th>
                <th width="15%" class="text-right">Unit Price</th>
                <th width="15%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Group items by description
                $itemsByDescription = [];
                
                // First pass: collect non-zero amount items
                foreach ($sale->items as $item) {
                    $description = $item->item_description;
                    $amount = (float)$item->amount;
                    
                    if ($amount != 0 || !isset($itemsByDescription[$description])) {
                        $itemsByDescription[$description] = $item;
                    }
                }
            @endphp
            
            @foreach($itemsByDescription as $item)
            <tr>
                <td class="text-center">{{ floor($item->quantity) == $item->quantity ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }}</td>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->item_description }}</td>
                <td class="text-right">
                    @if($item->quantity > 0)
                        ${{ number_format(abs($item->amount) / $item->quantity, 2) }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-right">${{ number_format(abs($item->amount), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="amount">${{ number_format(abs($sale->total_amount), 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="label" style="color: #111827;">Total:</td>
            <td class="amount">${{ number_format(abs($sale->total_amount), 2) }}</td>
        </tr>
    </table>

    <div class="thank-you">
        Thank you for your business!
    </div>

    <div class="footer">
        This document is a copy of the transaction from NetSuite.<br>
        Generated by: {{ $generatedBy }} | {{ $generatedAt->format('m/d/Y h:i A') }} | A&E Wholesale of North Florida
    </div>
</body>
</html>