{{-- <x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> --}}
<x-mail::message>
# Nouveau message de contact

- **Nom :** {{ $contact->name }}
- **Email :** {{ $contact->email }}

**Message :**
{{ $contact->message }}

Merci,<br>
DreamLearn
</x-mail::message>

