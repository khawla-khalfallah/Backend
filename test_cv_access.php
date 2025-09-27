<?php
// Test CV accessibility after storage link creation

echo "🔗 Testing CV Accessibility After Storage Link\n";
echo "===============================================\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Check if storage link exists
    $publicStoragePath = public_path('storage');
    $storageAppPublicPath = storage_path('app/public');
    
    echo "📂 Storage Path Analysis:\n";
    echo "Public storage link: $publicStoragePath\n";
    echo "Storage app/public: $storageAppPublicPath\n";
    echo "Storage link exists: " . (is_link($publicStoragePath) ? 'YES' : 'NO') . "\n";
    echo "Storage link target: " . (is_link($publicStoragePath) ? readlink($publicStoragePath) : 'N/A') . "\n\n";

    // Find a formateur with CV
    $formateur = \App\Models\Formateur::whereNotNull('cv')->with('user')->first();
    
    if (!$formateur) {
        echo "❌ No formateur found with CV\n";
        exit;
    }
    
    echo "👤 Found Formateur:\n";
    echo "Name: " . $formateur->user->nom . " " . $formateur->user->prenom . "\n";
    echo "Email: " . $formateur->user->email . "\n";
    echo "CV Path: " . $formateur->cv . "\n\n";
    
    // Check file existence
    $fullPath = storage_path('app/public/' . $formateur->cv);
    echo "📄 CV File Analysis:\n";
    echo "Full file path: $fullPath\n";
    echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($fullPath)) {
        echo "File size: " . formatBytes(filesize($fullPath)) . "\n";
        echo "MIME type: " . mime_content_type($fullPath) . "\n";
        echo "Last modified: " . date('Y-m-d H:i:s', filemtime($fullPath)) . "\n";
    }
    
    // Generate URL
    $cvUrl = \Illuminate\Support\Facades\Storage::url($formateur->cv);
    echo "\n🌐 Generated CV URL: $cvUrl\n";
    
    // Test direct access to public storage
    $publicFilePath = public_path('storage/' . $formateur->cv);
    echo "📱 Public file path: $publicFilePath\n";
    echo "Public file accessible: " . (file_exists($publicFilePath) ? 'YES' : 'NO') . "\n\n";
    
    echo "✅ Summary:\n";
    if (is_link($publicStoragePath) && file_exists($fullPath) && file_exists($publicFilePath)) {
        echo "🎉 CV should be accessible via: http://127.0.0.1:8000$cvUrl\n";
        echo "🔑 Try accessing this URL from your frontend!\n";
    } else {
        echo "⚠️  There are still issues to resolve:\n";
        if (!is_link($publicStoragePath)) echo "- Storage link is missing\n";
        if (!file_exists($fullPath)) echo "- CV file doesn't exist in storage\n";
        if (!file_exists($publicFilePath)) echo "- CV file not accessible via public link\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}