<?php
namespace App\Http\Controllers;

use App\Models\Apprenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprenantController extends Controller
{
    public function index()
    {
        return response()->json(Apprenant::with('user')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'prenom' => 'required|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => 'required|email|unique:users|regex:/^[\w\.-]+@([\w-]+\.)+[a-zA-Z]{2,}$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            'niveau_etude' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'apprenant'
            ]);

            $apprenant = Apprenant::create([
                'user_id' => $user->id, // Correspondance garantie
                'niveau_etude' => $validated['niveau_etude']
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Apprenant créé',
                'data' => $apprenant->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $apprenant = Apprenant::with(['user', 'formations', 'examens', 'certificats'])
                        ->findOrFail($id);
        return response()->json($apprenant);
    }

    public function update(Request $request, $user_id) // Maintenant utilisant user_id
    {
        $validated = $request->validate([
                'nom' => 'nullable|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
                'prenom' => 'nullable|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
                'email' => 'nullable|email|unique:users,email,'.$user_id . '|regex:/^[\w\.-]+@([\w-]+\.)+[a-zA-Z]{2,}$/',
                'password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                'niveau_etude' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $apprenant = Apprenant::where('user_id', $user_id)->firstOrFail();
            $user = $apprenant->user;
            // Mise à jour user
            if (isset($validated['nom'])) $user->nom = $validated['nom'];
            if (isset($validated['prenom'])) $user->prenom = $validated['prenom'];
            if (isset($validated['email'])) $user->email = $validated['email'];
            if (isset($validated['password'])) $user->password = bcrypt($validated['password']);
            $user->save();
            // Mise à jour apprenant
            if (isset($validated['niveau_etude'])) {
                $apprenant->niveau_etude = $validated['niveau_etude'];
                $apprenant->save();
            }

            DB::commit();
            return response()->json([
                'message' => 'Profil mis à jour',
                'data' => $apprenant->fresh('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur de mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($user_id)
    {
        DB::beginTransaction();
        try {
            $apprenant = Apprenant::where('user_id', $user_id)->firstOrFail();
            $user = $apprenant->user;

            $apprenant->delete();
            $user->delete();

            DB::commit();
            return response()->json(['message' => 'Suppression réussie']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur de suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function search(Request $request)
    // {
    //     $q = $request->query('q');
    //     $querySearch = array_map('trim', explode(',', $q)); // ['angular', 'react']

    //     $apprenants = Apprenant::query();

    //     // On ajoute un whereHas pour chaque mot-clé
    //     foreach ($querySearch as $term) {
    //         $apprenants->whereHas('inscrits.formation', function ($query) use ($term) {
    //             $query->where('titre', 'like', '%' . $term . '%');
    //         });
    //     }

    //     // On charge les relations
    //     $apprenants = $apprenants->with([
    //         'user',
    //         'inscrits.formation'
    //     ])->get();

    //     return response()->json($apprenants);
    // }
public function search(Request $request)
{
    $q = $request->query('q');
    $querySearch = array_map('trim', explode(',', $q)); // ex: ['laravel', 'python']

    $apprenants = Apprenant::with(['user', 'examens.formation']);

    // 👉 Si on recherche par formation
    if (!empty($q)) {
        foreach ($querySearch as $term) {
            $apprenants->whereHas('inscrits.formation', function ($query) use ($term) {
                $query->where('titre', 'like', '%' . $term . '%');
            });
        }
    }

    $apprenants = $apprenants->get();

    $result = $apprenants->map(function ($apprenant) use ($querySearch, $q) {
        $notes = [];

        foreach ($apprenant->examens as $examen) {
            if ($examen->formation) {
                // 👉 Cas recherche par formation : on garde uniquement les notes correspondantes
                if (!empty($q)) {
                    foreach ($querySearch as $term) {
                        if (stripos($examen->formation->titre, $term) !== false) {
                            $notes[$examen->formation->titre] = $examen->pivot->note ?? null;
                        }
                    }
                } else {
                    // 👉 Cas général : toutes les notes
                    $notes[$examen->formation->titre] = $examen->pivot->note ?? null;
                }
            }
        }

        return [
            'user_id' => $apprenant->user->id,
            'nom' => $apprenant->user->nom,
            'prenom' => $apprenant->user->prenom,
            'email' => $apprenant->user->email,
            'niveau_etude' => $apprenant->niveau_etude,
            'notes' => $notes
        ];
    });

    // 👉 Tri
    if (!empty($q)) {
        // Si recherche → trier par note de la formation
        $result = $result->sortByDesc(function ($a) {
            if (empty($a['notes'])) return -1;
            return collect($a['notes'])->first(); // une seule note par formation
        })->values();
    } else {
        // Sinon → trier par moyenne de toutes les notes
        $result = $result->sortByDesc(function ($a) {
            if (empty($a['notes'])) return -1;
            return collect($a['notes'])->avg();
        })->values();
    }

    return response()->json($result);
}
}