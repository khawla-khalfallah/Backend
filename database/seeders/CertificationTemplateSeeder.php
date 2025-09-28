<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CertificationTemplate;
use App\Models\Formation;
use App\Models\Examen;
use App\Models\Formateur;

class CertificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing formations and exams
        $formations = Formation::with('examens')->get();
        
        if ($formations->isEmpty()) {
            $this->command->warn('No formations found. Please seed formations first.');
            return;
        }

        foreach ($formations as $formation) {
            // Create certification template for each formation
            CertificationTemplate::create([
                'formation_id' => $formation->id,
                'formateur_id' => $formation->formateur_id,
                'examen_id' => $formation->examens->first()?->id, // Use first exam or null
                'titre_certification' => "Certificat de réussite - {$formation->titre}",
                'score_minimum' => 10.00, // 10/20 minimum score
                'description' => "Certificat automatique généré pour la formation {$formation->titre}",
                'is_active' => true,
            ]);
        }

        $this->command->info('Certification templates seeded successfully!');
    }
}