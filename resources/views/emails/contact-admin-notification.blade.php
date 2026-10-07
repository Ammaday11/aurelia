<x-mail::message>
# New Website Enquiry

You've received a new message through the Aurelia Boutonnière contact form.

**From:** {{ $contactMessage->name }}
**Email:** {{ $contactMessage->email }}
@if ($contactMessage->phone)
**Phone:** {{ $contactMessage->phone }}
@endif
@if ($contactMessage->subject)
**Subject:** {{ $contactMessage->subject }}
@endif

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

<x-mail::button :url="route('admin.messages.show', $contactMessage)">
View in Admin Panel
</x-mail::button>

Sent {{ $contactMessage->created_at->format('d M Y, g:ia') }}
</x-mail::message>
