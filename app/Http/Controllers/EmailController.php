<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail; // ou crée un Mailable spécifique
use App\Models\User;

class EmailController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'message' => 'required|string',
        ]);

        // Récupérer les utilisateurs
        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            // Envoyer l'email à chaque utilisateur
            Mail::to($user->email)->send(new TestMail($request->message));
        }

        return response()->json(['status' => 'success', 'message' => 'Emails envoyés avec succès']);
    }
}
