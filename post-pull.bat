@echo off
REM Post-pull setup script for Laravel project (Windows)
REM Run this after pulling changes from Git

echo 🚀 Starting post-pull setup...

REM Update dependencies if composer.lock changed
git diff HEAD~1 --name-only | findstr "composer.lock" >nul
if not errorlevel 1 (
    echo 📦 Updating Composer dependencies...
    composer install
)

REM Update npm dependencies if package-lock.json changed
git diff HEAD~1 --name-only | findstr "package-lock.json" >nul
if not errorlevel 1 (
    echo 📦 Updating NPM dependencies...
    npm install
)

REM Run migrations if migration files changed
git diff HEAD~1 --name-only | findstr "database/migrations/" >nul
if not errorlevel 1 (
    echo 📊 Running new migrations...
    php artisan migrate
)

REM Check and regenerate certificates
echo 🎓 Checking for certificates without PDFs...
php artisan certificates:regenerate

REM Clear caches
echo 🧹 Clearing caches...
php artisan config:clear
php artisan cache:clear

echo ✅ Post-pull setup completed!
echo 🌐 Start server with: php artisan serve
pause