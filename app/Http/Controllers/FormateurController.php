<?php

namespace App\Http\Controllers;

use App\Models\Formateur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FormateurController extends Controller
{
      // Afficher tous les formateurs
      public function index()
      {
          return response()->json(Formateur::with('user')->get());
      }
  
      // Créer un formateur
      public function store(Request $request)
      {
          $validated = $request->validate([
              'user_id' => 'required|exists:users,id|unique:formateurs,user_id',
              'specialite' => 'nullable|string|max:100',
              'bio' => 'nullable|string',
          ]);
  
          $formateur = Formateur::create($validated);
          return response()->json($formateur, 201);
      }
  
      // Afficher un formateur spécifique
      public function show($id)
      {
          $formateur = Formateur::with(['user', 'formations'])->findOrFail($id);
          return response()->json($formateur);
      }
  
      // Mettre à jour un formateur
      public function update(Request $request, $id)
      {
          $formateur = Formateur::findOrFail($id);
  
          $validated = $request->validate([
              'specialite' => 'nullable|string|max:100',
              'bio' => 'nullable|string',
          ]);
  
          $formateur->update($validated);
          return response()->json($formateur);
      }
  
      // Supprimer un formateur
      public function destroy($id)
      {
          Formateur::destroy($id);
          return response()->json(['message' => 'Formateur supprimé avec succès']);
      }
}
