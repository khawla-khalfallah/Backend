<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use App\Models\CertificationTemplate;
use App\Models\Formation;
use App\Models\Examen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FormateurCertificationController extends Controller
{
    /**
     * Get all certification templates for this formateur
     */
    public function getTemplates()
    {
        $formateurId = Auth::id();
        
        $templates = CertificationTemplate::where('formateur_id', $formateurId)
            ->with(['formation', 'examen'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($templates);
    }

    /**
     * Create a new certification template
     */
    public function storeTemplate(Request $request)
    {
        $formateurId = Auth::id();
        
        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'examen_id' => 'nullable|exists:examens,id',
            'titre_certification' => 'required|string|max:255',
            'score_minimum' => 'required|numeric|min:0|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Verify the formation belongs to this formateur
        $formation = Formation::where('id', $validated['formation_id'])
            ->where('formateur_id', $formateurId)
            ->first();
            
        if (!$formation) {
            return response()->json(['error' => 'Formation non autorisée'], 403);
        }

        $validated['formateur_id'] = $formateurId;
        
        $template = CertificationTemplate::create($validated);
        $template->load(['formation', 'examen']);

        return response()->json($template, 201);
    }

    /**
     * Get all formations with their templates and generated certificates
     */
    public function getFormationsWithCertifications()
    {
        $formateurId = Auth::id();
        
        $formations = Formation::where('formateur_id', $formateurId)
            ->with([
                'inscrits.apprenant.user',
                'examens',
                'certificats.apprenant.user'
            ])
            ->get();

        return response()->json($formations);
    }

    /**
     * Get generated certificates for this formateur
     */
    public function index()
    {
        $formateurId = Auth::id();
        
        $certificats = Certificat::where('formateur_id', $formateurId)
            ->with(['apprenant.user', 'formation', 'certificationTemplate'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($certificats);
    }

    /**
     * Download certificate PDF
     */
    public function downloadPdf($id)
    {
        $formateurId = Auth::id();
        
        $certificat = Certificat::where('id', $id)
            ->where('formateur_id', $formateurId)
            ->first();
            
        if (!$certificat || !$certificat->pdf_path) {
            return response()->json(['error' => 'Certificat PDF non trouvé'], 404);
        }

        if (!Storage::exists($certificat->pdf_path)) {
            return response()->json(['error' => 'Fichier PDF non trouvé'], 404);
        }

        return Storage::download($certificat->pdf_path);
    }

    /**
     * Get certification statistics
     */
    public function getStats()
    {
        $formateurId = Auth::id();
        
        $stats = [
            'total_templates' => CertificationTemplate::where('formateur_id', $formateurId)->count(),
            'active_templates' => CertificationTemplate::where('formateur_id', $formateurId)->where('is_active', true)->count(),
            'total_certificates' => Certificat::where('formateur_id', $formateurId)->count(),
            'certificates_this_month' => Certificat::where('formateur_id', $formateurId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count()
        ];

        return response()->json($stats);
    }
}
