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