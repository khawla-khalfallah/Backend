<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Models\Certificat;
use Illuminate\Http\Request;

class CertificatController extends Controller {
    public function index()
    {
        return Certificat::with('apprenant.user')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_obtention' => 'required|date',
            'apprenant_id' => 'required|exists:apprenants,user_id',
        ]);

        $certificat = Certificat::create($validated);
        return response()->json($certificat, 201);
    }

    public function show($id)
    {
        return Certificat::with('apprenant.user')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $certificat = Certificat::findOrFail($id);

        $validated = $request->validate([
            'date_obtention' => 'sometimes|date',
            'apprenant_id' => 'sometimes|exists:apprenants,user_id',
        ]);

        $certificat->update($validated);
         // Recharge la relation apprenant + user pour affichage correct
        $certificat->load('apprenant.user');
        return response()->json($certificat);
    }

    public function destroy($id)
    {
        $certificat = Certificat::findOrFail($id);
        $certificat->delete();

        return response()->json(['message' => 'Certificat supprimé.']);
    }
}
