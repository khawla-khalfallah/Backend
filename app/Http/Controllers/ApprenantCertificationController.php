<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use App\Models\Apprenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApprenantCertificationController extends Controller
{
    /**
     * Get all certificates for the authenticated apprenant
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'apprenant') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $apprenant = Apprenant::where('user_id', $user->id)->first();
        
        if (!$apprenant) {
            return response()->json(['error' => 'Apprenant profile not found'], 404);
        }

        $certificats = Certificat::where('apprenant_id', $apprenant->user_id)
            ->with(['formation', 'formateur.user', 'certificationTemplate'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($certificats);
    }

    /**
     * Download certificate PDF
     */
    public function downloadPdf($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'apprenant') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $apprenant = Apprenant::where('user_id', $user->id)->first();
        
        if (!$apprenant) {
            return response()->json(['error' => 'Apprenant profile not found'], 404);
        }

        $certificat = Certificat::where('id', $id)
            ->where('apprenant_id', $apprenant->user_id)
            ->first();
            
        if (!$certificat || !$certificat->pdf_path) {
            return response()->json(['error' => 'Certificat PDF non trouvé'], 404);
        }

        if (!Storage::exists($certificat->pdf_path)) {
            return response()->json(['error' => 'Fichier PDF non trouvé'], 404);
        }

        return Storage::download($certificat->pdf_path, 'certificat_' . $certificat->id . '.pdf');
    }

    /**
     * Get certificate details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'apprenant') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $apprenant = Apprenant::where('user_id', $user->id)->first();
        
        if (!$apprenant) {
            return response()->json(['error' => 'Apprenant profile not found'], 404);
        }

        $certificat = Certificat::where('id', $id)
            ->where('apprenant_id', $apprenant->user_id)
            ->with(['formation', 'formateur.user', 'certificationTemplate'])
            ->first();
            
        if (!$certificat) {
            return response()->json(['error' => 'Certificat non trouvé'], 404);
        }

        return response()->json($certificat);
    }
}
