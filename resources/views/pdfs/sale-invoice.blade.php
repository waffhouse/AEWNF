<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $sale->tran_id }}</title>
    <style>
        @page {
            margin: 48px;
            margin-bottom: 70px; /* Extra space for footer with page numbers */
        }
        
        /* Page number styling */
        .page-number {
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
        
        /* Page break inside avoidance */
        .item-row {
            page-break-inside: avoid;
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
                <!-- Logo and Invoice Details Row -->
                <td style="width: 50%; text-align: left; vertical-align: top; padding-bottom: 15px;">
                    <img src="{{ public_path(env('COMPANY_LOGO', 'images/AEWBlack.png')) }}" alt="{{ env('COMPANY_NAME', 'A&E Wholesale of North Florida') }}" class="logo">
                </td>
                <td style="width: 50%; text-align: right; vertical-align: top; padding-bottom: 15px;">
                    <div class="document-title">
                        {{ $sale->type }} #{{ $sale->tran_id }}
                    </div>
                    <div class="document-date">
                        Date: {{ $sale->date->format('F d, Y') }}
                    </div>
                </td>
            </tr>
            <tr style="height: auto; max-height: 150px;">
                <!-- Company Info Box -->
                <td style="width: 50%; text-align: left; vertical-align: top; padding-right: 10px;">
                    <div style="font-size: 11px; color: #4B5563; border: 1px solid #E5E7EB; padding: 8px; border-radius: 4px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 11px;">
                            <tr>
                                <td colspan="2" style="padding-bottom: 3px;">
                                    <div style="font-weight: bold; font-size: 12px; color: #111827;">COMPANY INFORMATION</div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding-right: 5px;">
                                    <div>{{ env('COMPANY_NAME', 'A&E Wholesale of North Florida') }}</div>
                                    <div>{{ env('COMPANY_STREET', '') }}{{ env('COMPANY_STREET_2') ? ', ' . env('COMPANY_STREET_2') : '' }}</div>
                                    <div>{{ env('COMPANY_CITY', '') }}, {{ env('COMPANY_STATE', '') }} {{ env('COMPANY_ZIP', '') }}</div>
                                </td>
                                <td style="width: 50%; vertical-align: top;">
                                    <div>Phone: {{ env('COMPANY_PHONE', '') }}</div>
                                    <div>{{ env('COMPANY_EMAIL', '') }}</div>
                                    
                                    @if(env('COMPANY_CWD_LICENSE', ''))
                                        <div style="font-weight: bold;">FL CWD #: {{ env('COMPANY_CWD_LICENSE', '') }}</div>
                                    @endif
                                </td>
                            </tr>
                            @if(env('COMPANY_TWD_LICENSE', '') || env('COMPANY_GA_LICENSE', ''))
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding-right: 5px;">
                                    @if(env('COMPANY_TWD_LICENSE', ''))
                                        <div style="font-weight: bold;">FL TWD #: {{ env('COMPANY_TWD_LICENSE', '') }}</div>
                                    @endif
                                </td>
                                <td style="width: 50%; vertical-align: top;">
                                    @if(env('COMPANY_GA_LICENSE', ''))
                                        <div style="font-weight: bold;">GA #: {{ env('COMPANY_GA_LICENSE', '') }}</div>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </td>
                
                <!-- Customer Info Box -->
                <td style="width: 50%; text-align: left; vertical-align: top; padding-left: 10px;">
                    <div style="font-size: 11px; color: #4B5563; border: 1px solid #E5E7EB; padding: 8px; border-radius: 4px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 11px;">
                            <tr>
                                <td colspan="2" style="padding-bottom: 3px;">
                                    <div style="font-weight: bold; font-size: 12px; color: #111827;">CUSTOMER INFORMATION</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-bottom: 3px;">
                                    <div style="font-weight: bold; font-size: 12px;">{{ $sale->customer_name }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding-right: 5px;">
                                    <div>ID: {{ $sale->entity_id }}</div>
                                    
                                    @if(isset($customer) && $customer->phone)
                                        <div>Phone: {{ $customer->phone }}</div>
                                    @endif
                                </td>
                                <td style="width: 50%; vertical-align: top;">
                                    @if(isset($customer) && $customer->county)
                                        <div>County: {{ $customer->county }}</div>
                                    @endif
                                    
                                    @if(isset($customer) && $customer->terms)
                                        <div>Terms: {{ $customer->terms }}</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-top: 2px;">
                                    @if(isset($customer) && $customer->license_number)
                                        <div style="font-weight: bold;">
                                            @if($customer->license_type)
                                                {{ $customer->license_type }} {{ $customer->license_number }}
                                            @else
                                                License # {{ $customer->license_number }}
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if(isset($customer) && $customer->shipping_address)
                                        <div>
                                            Address: 
                                            @php
                                                // Ultra-compact shipping address
                                                $addressParts = preg_split('/,\s*/', $customer->shipping_address);
                                                if (count($addressParts) > 0) {
                                                    // Show first part and zip code only
                                                    echo trim($addressParts[0]);
                                                    
                                                    if (count($addressParts) > 1) {
                                                        // Just add the last part (usually zip)
                                                        echo ", " . trim(end($addressParts));
                                                    }
                                                }
                                            @endphp
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- The customer info table has been moved to the header section -->

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
            <tr class="item-row">
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

    <script type="text/php">
        if (isset($pdf)) {
            // Single consolidated footer with dividers between elements
            $textSize = 9;  // Consistent small size
            $footerY = $pdf->get_height() - 25;  // Position for the footer
            
            // Thank you message - in red, bold
            $thankYouText = "Thank you for your business!";
            $thankYouFont = $fontMetrics->getFont("Helvetica", "bold");
            
            // Page numbers
            $pageNumText = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $regularFont = $fontMetrics->getFont("Helvetica");
            
            // Company info
            $companyName = "A&E Wholesale of North Florida";  // Hardcoded to avoid HTML entity issues
            $website = "aewnf.com";  // Hardcoded to avoid HTML entity issues
            
            // Create one combined string with dividers
            $footerText = "$thankYouText  |  $pageNumText  |  $companyName  |  $website";
            
            // Break the footer into separate parts for clean rendering
            $divider = "  |  ";
            $dividerWidth = $fontMetrics->get_text_width($divider, $regularFont, $textSize);
            
            // Get widths of each segment - ensure we use the right placeholder for page numbers
            $thankYouWidth = $fontMetrics->get_text_width($thankYouText, $thankYouFont, $textSize);
            
            // For page numbers, use a realistic width estimation since the actual numbers will vary
            $pageNumText = "Page 1 of 1"; // Use a representative placeholder for width calculation
            $pageNumWidth = $fontMetrics->get_text_width($pageNumText, $regularFont, $textSize);
            $pageNumText = "Page {PAGE_NUM} of {PAGE_COUNT}"; // Restore the real text with placeholders
            
            $companyNameWidth = $fontMetrics->get_text_width($companyName, $regularFont, $textSize);
            $websiteWidth = $fontMetrics->get_text_width($website, $regularFont, $textSize);
            
            // Calculate total width
            $totalWidth = $thankYouWidth + $pageNumWidth + $companyNameWidth + $websiteWidth + (3 * $dividerWidth);
            
            // Calculate starting X position to center the entire footer
            $startX = ($pdf->get_width() - $totalWidth) / 2;
            
            // Draw each part separately 
            // 1. Thank you in red and bold
            $pdf->page_text($startX, $footerY, $thankYouText, $thankYouFont, $textSize, array(0.94, 0.27, 0.27));
            $currentX = $startX + $thankYouWidth;
            
            // 2. First divider
            $pdf->page_text($currentX, $footerY, $divider, $regularFont, $textSize, array(0.41, 0.44, 0.50));
            $currentX += $dividerWidth;
            
            // 3. Page numbers
            $pdf->page_text($currentX, $footerY, $pageNumText, $regularFont, $textSize, array(0.41, 0.44, 0.50));
            $currentX += $pageNumWidth;
            
            // 4. Second divider
            $pdf->page_text($currentX, $footerY, $divider, $regularFont, $textSize, array(0.41, 0.44, 0.50));
            $currentX += $dividerWidth;
            
            // 5. Company name
            $pdf->page_text($currentX, $footerY, $companyName, $regularFont, $textSize, array(0.41, 0.44, 0.50));
            $currentX += $companyNameWidth;
            
            // 6. Third divider
            $pdf->page_text($currentX, $footerY, $divider, $regularFont, $textSize, array(0.41, 0.44, 0.50));
            $currentX += $dividerWidth;
            
            // 7. Website
            $pdf->page_text($currentX, $footerY, $website, $regularFont, $textSize, array(0.41, 0.44, 0.50));
        }
    </script>
</body>
</html>