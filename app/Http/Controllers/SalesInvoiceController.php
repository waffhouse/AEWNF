<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class SalesInvoiceController extends Controller
{
    /**
     * Generate an invoice PDF for a sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Generate an invoice PDF for a sale.
     *
     * This method is protected by both permission middleware and customer verification middleware,
     * but we keep the explicit checks here as an additional security layer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateInvoice($id)
    {
        // Get the sale with all its items
        $sale = Sale::with(['items'])->findOrFail($id);

        // Get the full customer record if available
        $customer = Customer::where('entity_id', $sale->entity_id)->first();

        // Security check: If user is not an admin with 'view netsuite sales data' permission
        // they must be the owner of this sale (entity_id must match their customer_number)
        $user = Auth::user();
        if (! $user->hasPermissionTo('view netsuite sales data')) {
            // If user doesn't have customer_number or it doesn't match sale's entity_id
            if (! $user->customer_number || $sale->entity_id !== $user->customer_number) {
                abort(403, 'You are not authorized to view this invoice');
            }
        }

        // Create the PDF
        $pdf = Pdf::loadView('pdfs.sale-invoice', [
            'sale' => $sale,
            'customer' => $customer,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
        ]);

        // Enable DomPDF settings for page numbers
        $pdf->setOption('isPhpEnabled', true);

        // Stream the PDF with a dynamic filename
        return $pdf->stream("sale-{$sale->tran_id}-invoice.pdf");
    }
}
