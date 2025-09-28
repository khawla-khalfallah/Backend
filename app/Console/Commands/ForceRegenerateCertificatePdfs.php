<?php

namespace App\Console\Commands;

use App\Models\Certificat;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ForceRegenerateCertificatePdfs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'certificates:force-regenerate';

    /**
     * The console command description.
     */
    protected $description = 'Force regenerate all certificate PDFs with new template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting forced certificate PDF regeneration...');

        // Get all certificates
        $certificates = Certificat::with([
                'apprenant.user', 
                'formation', 
                'formateur.user',
                'certificationTemplate'
            ])
            ->get();

        if ($certificates->isEmpty()) {
            $this->info('No certificates found.');
            return 0;
        }

        $this->info("Found {$certificates->count()} certificates to regenerate.");

        if (!$this->confirm('Do you want to regenerate ALL certificate PDFs?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($certificates->count());
        $progressBar->start();

        $successful = 0;
        $failed = 0;

        foreach ($certificates as $certificat) {
            try {
                // Delete old PDF if exists
                if ($certificat->pdf_path && Storage::exists($certificat->pdf_path)) {
                    Storage::delete($certificat->pdf_path);
                }
                
                $this->generateCertificatePdf($certificat);
                $successful++;
            } catch (\Exception $e) {
                $this->error("\nFailed to generate PDF for certificate ID {$certificat->id}: " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n\nForced PDF regeneration completed!");
        $this->info("✅ Successful: {$successful}");
        $this->info("❌ Failed: {$failed}");

        return 0;
    }

    /**
     * Generate PDF for a certificate
     */
    private function generateCertificatePdf(Certificat $certificat): void
    {
        $data = [
            'certificat' => $certificat,
            'apprenant' => $certificat->apprenant,
            'user' => $certificat->apprenant->user,
            'formation' => $certificat->formation,
            'formateur' => $certificat->formateur->user,
            'date_generation' => now(),
            'platform_name' => 'Dream Learn'
        ];

        $pdf = Pdf::loadView('certificates.simple-template', $data)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true
            ]);
        
        // Save PDF
        $fileName = 'certificate_' . $certificat->id . '_' . time() . '.pdf';
        $path = 'certificates/' . $fileName;
        
        // Ensure certificates directory exists
        if (!Storage::exists('certificates')) {
            Storage::makeDirectory('certificates');
        }
        
        Storage::put($path, $pdf->output());
        
        // Update certificate with PDF path
        $certificat->update(['pdf_path' => $path]);
    }
}