## 🎯 QUICK FIX FOR CERTIFICATE NULL PATH ISSUE

### The Problem
When you pull the project, certificates in the database have `pdf_path` values that point to files on the original developer's machine, which don't exist on your computer.

### The Solution (Choose One)

#### ✅ Option 1: Automatic Setup (RECOMMENDED)
```bash
php artisan project:setup
```
This single command will:
- Run any pending migrations
- Create missing storage directories  
- Regenerate all missing certificate PDFs
- Clear caches
- Show you a status report

#### ✅ Option 2: Manual Setup
```bash
# 1. Run migrations
php artisan migrate

# 2. Create storage link
php artisan storage:link

# 3. Regenerate missing certificate PDFs
php artisan certificates:regenerate

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
```

### After Every Git Pull
Run the automated script:
```bash
# Windows
post-pull.bat

# Linux/Mac  
./post-pull.sh
```

### Why This Happens
- Certificate PDFs are stored in `storage/app/certificates/`
- Database contains relative paths like `certificates/certificate_123_456.pdf`
- When you pull the repo, you get the database records but not the actual PDF files
- Our commands regenerate the missing PDFs automatically

### Verification
After running the setup, you should see:
- ✅ Certificates download properly from the frontend
- ✅ No "null" paths in the certificate database
- ✅ PDFs are generated and stored correctly

Need help? Check `README_SETUP.md` for detailed instructions.