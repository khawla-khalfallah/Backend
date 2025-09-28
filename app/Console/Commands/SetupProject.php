<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Certificat;
use App\Models\CertificationTemplate;
use App\Models\Formation;
use Illuminate\Support\Facades\Storage;

class SetupProject extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'project:setup {--fresh : Fresh installation with sample data}';

    /**
     * The console command description.
     */
    protected $description = 'Setup project for new contributors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up project...');

        // Check if fresh install is requested
        if ($this->option('fresh')) {
            if ($this->confirm('⚠️  This will delete all existing data. Continue?')) {
                $this->call('migrate:fresh', ['--seed' => true]);
            } else {
                return;
            }
        } else {
            // Run migrations
            $this->info('📊 Running migrations...');
            $this->call('migrate');
        }

        // Create storage directories
        $this->info('📁 Creating storage directories...');
        $directories = ['certificates', 'pdfs', 'videos'];
        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
                $this->info("✅ Created directory: storage/app/{$dir}");
            }
        }

        // Create storage link
        $this->info('🔗 Creating storage link...');
        $this->call('storage:link');

        // Clear caches
        $this->info('🧹 Clearing caches...');
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('route:clear');

        // Regenerate certificates if any exist with null pdf_path
        $nullCertificates = Certificat::whereNull('pdf_path')->count();
        if ($nullCertificates > 0) {
            $this->info("🎓 Found {$nullCertificates} certificates without PDFs. Regenerating...");
            $this->call('certificates:regenerate');
        }

        // Show status
        $this->showProjectStatus();

        $this->info('✅ Project setup completed successfully!');
        $this->info('🌐 You can now start the server with: php artisan serve');
    }

    private function showProjectStatus()
    {
        $this->info("\n📋 Project Status:");
        $this->table(['Component', 'Status', 'Count'], [
            ['Formations', '✅ Ready', Formation::count()],
            ['Certification Templates', '✅ Ready', CertificationTemplate::count()],
            ['Certificates (Total)', '✅ Ready', Certificat::count()],
            ['Certificates (With PDF)', '✅ Ready', Certificat::whereNotNull('pdf_path')->count()],
            ['Certificates (Missing PDF)', Certificat::whereNull('pdf_path')->count() > 0 ? '⚠️  Needs Fix' : '✅ OK', Certificat::whereNull('pdf_path')->count()],
        ]);
    }
}