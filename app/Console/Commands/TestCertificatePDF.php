<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Formation;
use App\Models\Formateur;

class TestCertificatePDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:certificate-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test certificate PDF generation in landscape format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing landscape certificate PDF generation...');

        try {
            // Create mock data for testing
            $mockData = [
                'user' => (object) [
                    'prenom' => 'Jean',
                    'nom' => 'Dupont'
                ],
                'formation' => (object) [
                    'titre' => 'Formation React Avancée',
                    'duree' => '40'
                ],
                'formateur' => (object) [
                    'prenom' => 'Marie',
                    'nom' => 'Martin'
                ],
                'certificat' => (object) [
                    'id' => 1,
                    'titre_certification' => 'Certificat de réussite - Formation React Avancée',
                    'date_obtention' => now(),
                    'note_examen' => 16.5
                ],
                'date_generation' => now(),
                'platform_name' => 'Dream Learn'
            ];

            // Generate PDF with landscape orientation
            $pdf = Pdf::loadView('certificates.landscape-template', $mockData)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'serif',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true
                ]);

            // Save test PDF
            $fileName = 'test_certificate_landscape_' . time() . '.pdf';
            $path = 'certificates/test/' . $fileName;
            
            Storage::put($path, $pdf->output());

            $this->info("✅ Landscape certificate PDF generated successfully!");
            $this->info("📄 Saved to: storage/app/{$path}");
            $this->info("🎨 Template: certificates.landscape-template");
            $this->info("📐 Format: A4 Landscape");

        } catch (\Exception $e) {
            $this->error("❌ Failed to generate certificate PDF: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
