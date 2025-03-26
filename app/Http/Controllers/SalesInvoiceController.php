<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesInvoiceController extends Controller
{
    /**
     * Generate an invoice PDF for a sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateInvoice($id)
    {
        // Get the sale with all its items
        $sale = Sale::with('items')->findOrFail($id);
        
        // Security check: If user is not an admin with 'view netsuite sales data' permission
        // they must be the owner of this sale (entity_id must match their customer_number)
        if (!Auth::user()->hasPermissionTo('view netsuite sales data')) {
            // If user doesn't have customer_number or it doesn't match sale's entity_id
            if (!Auth::user()->customer_number || $sale->entity_id !== Auth::user()->customer_number) {
                abort(403, 'You are not authorized to view this invoice');
            }
        }
        
        // Create the PDF
        $pdf = Pdf::loadView('pdfs.sale-invoice', [
            'sale' => $sale,
            'generatedBy' => Auth::user()->name,
            'generatedAt' => now(),
        ]);
        
        // Stream the PDF with a dynamic filename
        return $pdf->stream("sale-{$sale->tran_id}-invoice.pdf");
    }
}