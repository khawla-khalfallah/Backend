<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        return Contact::all();
    }
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'prenom'  => 'required|string|max:100',  
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
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply_message' => 'required|string',
        ]);

        $contact = Contact::findOrFail($id);

        // Envoi d'email à la personne
        Mail::raw($request->reply_message, function ($message) use ($contact) {
            $message->to($contact->email)
                    ->subject("Réponse à votre message sur DreamLearn");
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Réponse envoyée avec succès'
        ]);
    }

}
