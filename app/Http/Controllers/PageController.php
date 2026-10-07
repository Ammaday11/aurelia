<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function submitContact(ContactRequest $request): RedirectResponse
    {
        $message = ContactMessage::create([
            ...$request->safe()->except(['website']),
            'submitted_from_ip' => $request->ip(),
        ]);

        $adminEmail = setting('contact_email', config('mail.from.address'));

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new ContactMessageReceived($message));
            } catch (\Throwable $e) {
                // The message is already safely stored in the database;
                // never let a mail transport hiccup block the visitor.
                Log::warning('Failed to send contact notification email: '.$e->getMessage());
            }
        }

        return back()->with('success', 'Thank you! Your message has been sent — we\'ll be in touch soon.');
    }
}
