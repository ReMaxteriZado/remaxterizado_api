<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\ContactForm;

class ZaidaWebsiteController extends Controller
{
    public function sendContactEmail(Request $request)
    {
        try {
            // Send the welcome email
            Notification::route('mail', 'zaidaonly@gmail.com')->notify(new ContactForm($request->all()));

            // Return a response indicating the email has been sent
            return response()->json(['message' => 'Welcome email sent'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error sending email'], 500);
        }
    }
}
