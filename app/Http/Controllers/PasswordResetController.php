<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    // 1️⃣ Demande de reset (envoi email)
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email non trouvé.'], 404);
        }

        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        // Envoi email avec lien de réinitialisation
        Mail::raw("Voici votre lien de réinitialisation : http://localhost:3000/reset-password/$token", function ($message) use ($request) {
            $message->to($request->email)->subject('Réinitialisation de mot de passe');
        });

        return response()->json(['message' => 'Email de réinitialisation envoyé.']);
    }

    // 2️⃣ Réinitialisation du mot de passe
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $reset = DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->token
        ])->first();

        if (!$reset) {
            return response()->json(['message' => 'Token invalide.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
