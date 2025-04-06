<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        try {
            // Send email to company
            Mail::send('emails.contact', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? 'Not provided',
                'userMessage' => $validated['message'],
            ], function ($message) use ($validated) {
                $message->from($validated['email'], $validated['name']);
                $message->to(env('COMPANY_EMAIL'));
                $message->subject('New Contact Form Submission from ' . $validated['name']);
            });

            return redirect()->back()->with('success', 'Thank you for your message. We will contact you shortly!');
        } catch (\Exception $e) {
            Log::error('Contact form email failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Sorry, there was a problem sending your message. Please try again later or contact us directly.')->withInput();
        }
    }
}
