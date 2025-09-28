# Project Setup Instructions

## For New Contributors/Collaborators

When you pull this project for the first time or after database changes, follow these steps:

### 1. Basic Setup
```bash
# Clone the repository
git clone [repository-url]
cd Backend

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

### 2. Database Setup
```bash
# Create database (MySQL)
# Make sure to create a database named 'pfe_khawla' or update .env

# Run migrations
php artisan migrate

# Seed the database (optional - for test data)
php artisan db:seed
```

### 3. Certificate System Setup
**IMPORTANT**: After pulling changes, certificates may have null pdf_path. Fix this by running:

```bash
# ONE-COMMAND SETUP (Recommended for new collaborators)
php artisan project:setup

# OR Manual setup:
# Regenerate missing certificate PDFs
php artisan certificates:regenerate

# OR force regenerate ALL certificates (if needed)
php artisan certificates:force-regenerate
```

### 4. Storage Setup
```bash
# Create storage link for public access
php artisan storage:link

# Make sure certificates directory exists
mkdir storage/app/certificates
```

### 5. Start Development Server
```bash
php artisan serve
# Visit: http://localhost:8000
```

## Available Commands

### Certificate System
- `php artisan project:setup` - Complete project setup (recommended)
- `php artisan certificates:regenerate` - Regenerate PDFs for certificates with null pdf_path  
- `php artisan certificates:force-regenerate` - Force regenerate ALL certificate PDFs

### Database
- `php artisan migrate:fresh --seed` - Fresh database with sample data (⚠️ Destroys existing data)
- `php artisan db:seed --class=CertificationTemplateSeeder` - Add certification templates

## Quick Setup Scripts

### Windows Users:
```cmd
# After git pull, run:
post-pull.bat
```

### Linux/Mac Users:
```bash
# Make executable first time:
chmod +x post-pull.sh

# After git pull, run:
./post-pull.sh
```

## Common Issues

### ❌ Certificate PDFs showing null/not working:
**Solution:** `php artisan certificates:regenerate`

### ❌ Storage permission errors (Linux/Mac):
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### ❌ Database connection errors:
**Solution:** Update your `.env` file with correct database credentials

### ❌ "Class not found" errors:
**Solution:** `composer install` or `composer dump-autoload`

### ❌ Certificate download not working:
1. Check if storage link exists: `php artisan storage:link`
2. Regenerate certificates: `php artisan certificates:regenerate`

## Project Structure Notes

- Certificates are stored in `storage/app/certificates/`
- PDF paths in database are relative: `certificates/certificate_123_456.pdf`
- Download URLs use Laravel Storage facade for proper path resolution
- All certificate generation is automatic when students pass exams