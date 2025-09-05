<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller {
    public function index()
    {
        return Pdf::with('formation')->get();
    }

    public function store(Request $request)
    {
         // Validation des champs
    $validated = $request->validate([
        'titre' => 'required|string|max:255',
        'fichier' => 'required|file|mimes:pdf|max:102400', 
        'formation_id' => 'required|exists:formations,id',
    ]);

    // Gestion du fichier téléchargé
    $file = $request->file('fichier');
    $filePath = $file->storeAs('pdfs', $file->getClientOriginalName(), 'public'); // Enregistre dans 'storage/app/public/pdfs'

    // Création du PDF dans la base de données
    $pdf = Pdf::create([
        'titre' => $validated['titre'],
        'fichier' => $filePath,
        'formation_id' => $validated['formation_id'],
    ]);

    return response()->json($pdf, 201);
    }

    public function show($id)
    {
        return Pdf::with('formation')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $pdf = Pdf::findOrFail($id);
    
        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'fichier' => 'nullable|file|mimes:pdf|max:102400', // ✅ pas "string", mais bien fichier
            'formation_id' => 'sometimes|exists:formations,id',
        ]);
    
        // 📎 Si un nouveau fichier a été envoyé
        if ($request->hasFile('fichier')) {
            // Supprimer l'ancien fichier s'il existe (optionnel)
            if ($pdf->fichier && Storage::disk('public')->exists($pdf->fichier)) {
                Storage::disk('public')->delete($pdf->fichier);
            }
    
            $file = $request->file('fichier');
            $filePath = $file->storeAs('pdfs', $file->getClientOriginalName(), 'public');
            $validated['fichier'] = $filePath;
        }
    
        $pdf->update($validated);
        return response()->json($pdf);
    }
    public function getByFormateur($id)
    {
        // Récupère uniquement les PDFs des formations créées par ce formateur
        return Pdf::whereHas('formation', function ($q) use ($id) {
            $q->where('formateur_id', $id);
        })
        ->with('formation:id,titre,formateur_id') // on limite les colonnes utiles
        ->get(['id', 'titre', 'fichier', 'formation_id']); // ✅ on force le retour de l'id
    }



    public function destroy($id)
    {
        $pdf = Pdf::findOrFail($id);
        $pdf->delete();

        return response()->json(['message' => 'PDF supprimé.']);
    }
}
