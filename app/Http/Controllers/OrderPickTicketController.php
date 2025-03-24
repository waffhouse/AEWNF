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
        // Get the order with all its items and the user
        $order = Order::with(['items.inventory', 'user'])->findOrFail($id);
        
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