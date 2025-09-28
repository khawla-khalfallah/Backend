<?php
// Test file for certification functionality
// Place this in Backend directory and run: php test_certification.php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Formateur;
use App\Models\Formation;
use App\Models\Apprenant;
use App\Models\Certificat;
use Illuminate\Http\Request;

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test Certification System ===\n\n";

try {
    // 1. Test getting formateur formations with students
    echo "1. Testing formateur formations with students...\n";
    $formateur = Formateur::with('formations.inscrits.apprenant.user')->first();
    
    if ($formateur) {
        echo "Formateur: " . $formateur->user->prenom . " " . $formateur->user->nom . "\n";
        echo "Formations count: " . $formateur->formations->count() . "\n";
        
        foreach ($formateur->formations as $formation) {
            echo "- Formation: " . $formation->titre . " (" . $formation->inscrits->count() . " inscrits)\n";
        }
    } else {
        echo "No formateur found in database\n";
    }
    
    echo "\n";

    // 2. Test certificate creation
    echo "2. Testing certificate creation...\n";
    
    if ($formateur && $formateur->formations->count() > 0) {
        $formation = $formateur->formations->first();
        
        if ($formation->inscrits->count() > 0) {
            $apprenant = $formation->inscrits->first()->apprenant;
            
            // Check if certificate already exists
            $existingCert = Certificat::where('apprenant_id', $apprenant->user_id)
                ->where('formation_id', $formation->id)
                ->first();
                
            if (!$existingCert) {
                $certificat = Certificat::create([
                    'apprenant_id' => $apprenant->user_id,
                    'formation_id' => $formation->id,
                    'formateur_id' => $formateur->user_id,
                    'titre_certification' => "Certificat de réussite - " . $formation->titre,
                    'note_examen' => 16.75,
                    'date_obtention' => now()->toDateString()
                ]);
                
                echo "Certificate created successfully!\n";
                echo "ID: " . $certificat->id . "\n";
                echo "Student: " . $apprenant->user->prenom . " " . $apprenant->user->nom . "\n";
                echo "Formation: " . $formation->titre . "\n";
                echo "Score: " . $certificat->note_examen . "/20\n";
            } else {
                echo "Certificate already exists (ID: " . $existingCert->id . ")\n";
            }
        } else {
            echo "No students enrolled in formation\n";
        }
    }
    
    echo "\n";

    // 3. Test certificate with relationships
    echo "3. Testing certificate relationships...\n";
    
    $certificat = Certificat::with(['apprenant.user', 'formation', 'formateur.user'])->first();
    
    if ($certificat) {
        echo "Certificate ID: " . $certificat->id . "\n";
        echo "Student: " . $certificat->apprenant->user->prenom . " " . $certificat->apprenant->user->nom . "\n";
        echo "Formation: " . $certificat->formation->titre . "\n";
        echo "Formateur: " . $certificat->formateur->user->prenom . " " . $certificat->formateur->user->nom . "\n";
        echo "Title: " . $certificat->titre_certification . "\n";
        echo "Score: " . ($certificat->note_examen ?? 'N/A') . "/20\n";
        echo "Date: " . $certificat->date_obtention . "\n";
        echo "PDF Status: " . ($certificat->pdf_path ? 'Generated' : 'Not generated') . "\n";
    } else {
        echo "No certificates found\n";
    }
    
    echo "\n";

    // 4. Test API data structure
    echo "4. Testing API data structure...\n";
    
    $formations = Formation::where('formateur_id', $formateur->user_id ?? 1)
        ->with([
            'inscrits.apprenant.user',
            'examens' => function($query) {
                $query->with('apprenants.user');
            }
        ])
        ->get();

    echo "API-ready formations data:\n";
    echo "Count: " . $formations->count() . "\n";
    
    foreach ($formations as $formation) {
        echo "- " . $formation->titre . " (students: " . $formation->inscrits->count() . ", exams: " . $formation->examens->count() . ")\n";
    }
    
    echo "\n=== Test completed successfully! ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}