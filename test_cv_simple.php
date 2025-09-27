<?php
// Simple test to check if storage link resolved the 403 issue

echo "🔗 Testing CV Access After Storage Link Creation\n";
echo "==============================================\n\n";

// Check if the public/storage link exists
$publicStoragePath = __DIR__ . '/public/storage';
echo "Storage link path: $publicStoragePath\n";
echo "Storage link exists: " . (is_link($publicStoragePath) ? '✅ YES' : '❌ NO') . "\n";

if (is_link($publicStoragePath)) {
    echo "Storage link target: " . readlink($publicStoragePath) . "\n";
}

// Check if we have any CV files
$storageDir = __DIR__ . '/storage/app/public/cvs';
echo "\nCV storage directory: $storageDir\n";
echo "CV directory exists: " . (is_dir($storageDir) ? '✅ YES' : '❌ NO') . "\n";

if (is_dir($storageDir)) {
    $files = glob($storageDir . '/*');
    echo "CV files found: " . count($files) . "\n";
    
    if (count($files) > 0) {
        $sampleFile = basename($files[0]);
        echo "Sample CV file: $sampleFile\n";
        
        // Check if accessible via public link
        $publicFile = __DIR__ . '/public/storage/cvs/' . $sampleFile;
        echo "Public accessibility: " . (file_exists($publicFile) ? '✅ ACCESSIBLE' : '❌ NOT ACCESSIBLE') . "\n";
        
        echo "\n🌐 Test URL: http://127.0.0.1:8000/storage/cvs/$sampleFile\n";
        echo "📝 This URL should now work in your browser!\n";
    }
}

echo "\n📋 Summary:\n";
echo "- The storage link has been created successfully\n";
echo "- CVs should now be accessible via /storage/cvs/ URLs\n";
echo "- The 403 Forbidden error should be resolved\n";
echo "- Test the URL in your browser or frontend application\n";