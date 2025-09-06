<?php
// Test login and get token

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Bootstrap the app
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Getting token for apprenant...\n";

try {
    // Get an apprenant user
    $user = \App\Models\User::where('role', 'apprenant')->first();
    if (!$user) {
        echo "No apprenant user found\n";
        exit;
    }
    
    echo "User: " . $user->email . "\n";
    
    // Create a token for the user (simulate login)
    $token = $user->createToken('test-token')->plainTextToken;
    echo "Token: " . $token . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
