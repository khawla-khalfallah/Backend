#!/bin/bash

# Post-pull setup script for Laravel project
# Run this after pulling changes from Git

echo "🚀 Starting post-pull setup..."

# Update dependencies if composer.lock changed
if git diff HEAD~1 --name-only | grep -q "composer.lock"; then
    echo "📦 Updating Composer dependencies..."
    composer install
fi

# Update npm dependencies if package-lock.json changed
if git diff HEAD~1 --name-only | grep -q "package-lock.json"; then
    echo "📦 Updating NPM dependencies..."
    npm install
fi

# Run migrations if migration files changed
if git diff HEAD~1 --name-only | grep -q "database/migrations/"; then
    echo "📊 Running new migrations..."
    php artisan migrate
fi

# Regenerate certificates if needed
CERTIFICATES_COUNT=$(php artisan tinker --execute="echo App\\Models\\Certificat::whereNull('pdf_path')->count();")
if [ "$CERTIFICATES_COUNT" -gt 0 ]; then
    echo "🎓 Found $CERTIFICATES_COUNT certificates without PDFs. Regenerating..."
    php artisan certificates:regenerate
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear

echo "✅ Post-pull setup completed!"
echo "🌐 Start server with: php artisan serve"