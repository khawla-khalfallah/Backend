<?php

namespace App\Console\Commands;

use App\Models\Certificat;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class RegenerateCertificatePdfs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'certificates:regenerate';

    /**
     * The console command description.
     */
    protected $description = 'Regenerate PDF files for certificates that have null pdf_path';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting certificate PDF regeneration...');

        // Find certificates with null pdf_path
        $certificates = Certificat::whereNull('pdf_path')
            ->with([
                'apprenant.user', 
                'formation', 
                'formateur.user',
                'certificationTemplate'
            ])
            ->get();

        if ($certificates->isEmpty()) {
            $this->info('No certificates found with missing PDF files.');
            return 0;
        }

        $this->info("Found {$certificates->count()} certificates without PDF files.");

        $progressBar = $this->output->createProgressBar($certificates->count());
        $progressBar->start();

        $successful = 0;
        $failed = 0;

        foreach ($certificates as $certificat) {
            try {
                $this->generateCertificatePdf($certificat);
                $successful++;
            } catch (\Exception $e) {
                $this->error("\nFailed to generate PDF for certificate ID {$certificat->id}: " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n\nPDF regeneration completed!");
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
                'defaultFont' => 'serif',
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
