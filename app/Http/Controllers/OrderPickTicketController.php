<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderPickTicketController extends Controller
{
    // Middleware is applied in the route definition instead

    /**
     * Generate a pick ticket PDF for an order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generatePickTicket($id)
    {
        // Get the order with all its items, the user, and the customer
        $order = Order::with(['items.inventory', 'user.customer'])->findOrFail($id);
        
        // Security check: Ensure the user has permission to manage orders
        // This check is redundant with the middleware but adds an extra layer of security
        if (!Auth::user()->hasPermissionTo('manage orders')) {
            abort(403, 'You are not authorized to generate pick tickets');
        }
        
        // Create the PDF
        $pdf = Pdf::loadView('pdfs.pick-ticket', [
            'order' => $order,
            'generatedBy' => Auth::user()->name,
            'generatedAt' => now(),
        ]);
        
        // Stream the PDF with a dynamic filename
        return $pdf->stream("order-{$order->id}-pick-ticket.pdf");
    }
}