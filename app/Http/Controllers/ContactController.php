<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'message' => 'required|string',
        ]);

        // Sauvegarde en base
        $contact = Contact::create($validated);

        // Envoi d’email
        Mail::to("khalfallahkhawlakh@gmail.com")->send(new ContactMail($contact));

        return response()->json([
            'status'  => 'success',
            'message' => 'Message envoyé avec succès',
            'data'    => $contact
        ]);
    }
}
