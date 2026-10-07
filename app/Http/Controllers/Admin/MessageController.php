<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ContactMessage::class);

        return view('admin.messages.index', [
            'title' => 'Messages',
            'messages' => ContactMessage::query()->latest()->paginate(20),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        $this->authorize('view', $contactMessage);

        $contactMessage->markAsRead();

        return view('admin.messages.show', [
            'title' => 'Message from '.$contactMessage->name,
            'message' => $contactMessage,
        ]);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
