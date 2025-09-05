<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.',
                'errors' => [
                    'email' => ['Les informations fournies sont incorrectes.']
                ]
            ], 401);
        }
        
        // 🔒 Vérifier si c’est un formateur en attente ou refusé
        if ($user->role === 'formateur' && $user->formateur) {
            if ($user->formateur->status === 'en_attente') {
                return response()->json([
                    'message' => '⏳ Votre compte est en cours de vérification par l’administrateur.'
                ], 403);
            }

            if ($user->formateur->status === 'refuse') {
                return response()->json([
                    'message' => '❌ Votre inscription a été refusée.',
                    'remarque' => $user->formateur->remarque // tu renvoies la raison
                ], 403);
            }
        }
        return response()->json([
            'token' => $user->createToken('login')->plainTextToken,
            'user' => $user->load(['apprenant', 'formateur', 'recruteur', 'administrateur']),
        ]);
        } 
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur interne : ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
