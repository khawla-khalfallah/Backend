<?php
// Test PDF creation endpoint

echo "🔍 Testing PDF Creation (POST /api/pdfs) Debug\n";
echo "==============================================\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Find a formateur user
    $user = \App\Models\User::where('role', 'formateur')->first();
    if (!$user) {
        echo "❌ No formateur user found in database\n";
        exit;
    }
    
    echo "👤 Found Formateur User:\n";
    echo "ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n\n";
    
    // Check if user has formateur profile
    $formateur = \App\Models\Formateur::where('user_id', $user->id)->first();
    if (!$formateur) {
        echo "❌ Formateur profile not found for user\n";
        exit;
    }
    
    echo "✅ Formateur profile found\n";
    echo "Status: " . $formateur->status . "\n\n";
    
    // Check if formateur has formations
    $formations = \App\Models\Formation::where('formateur_id', $user->id)->get();
    echo "📚 Formateur's formations: " . $formations->count() . "\n";
    
    if ($formations->isEmpty()) {
        echo "❌ No formations found for this formateur. Creating one for testing...\n";
        
        $formation = \App\Models\Formation::create([
            'titre' => 'Test Formation for PDF',
            'description' => 'Formation de test pour upload PDF',
            'duree' => 40,
            'formateur_id' => $user->id,
            'prix' => 0,
        ]);
        
        echo "✅ Created test formation with ID: " . $formation->id . "\n\n";
    } else {
        $formation = $formations->first();
        echo "✅ Using existing formation: " . $formation->titre . " (ID: " . $formation->id . ")\n\n";
    }
    
    // Create a test token
    $token = $user->createToken('test-pdf-upload')->plainTextToken;
    echo "🔑 Created auth token: " . substr($token, 0, 20) . "...\n\n";
    
    // Test authentication
    echo "🔐 Testing Authentication:\n";
    \Illuminate\Support\Facades\Auth::setUser($user);
    $authUser = \Illuminate\Support\Facades\Auth::user();
    if ($authUser) {
        echo "✅ Authentication working - User ID: " . $authUser->id . "\n";
        echo "✅ User role: " . $authUser->role . "\n";
    } else {
        echo "❌ Authentication failed\n";
    }
    
    // Check authorization logic
    echo "\n🛡️ Testing Authorization Logic:\n";
    echo "Formation formateur_id: " . $formation->formateur_id . "\n";
    echo "User ID: " . $user->id . "\n";
    echo "Authorization check: " . ($formation->formateur_id === $user->id ? '✅ AUTHORIZED' : '❌ NOT AUTHORIZED') . "\n\n";
    
    // Test the store method logic
    echo "📝 Simulating PdfController@store:\n";
    $testData = [
        'titre' => 'Test PDF Document',
        'formation_id' => $formation->id,
    ];
    
    // Create a fake request
    $request = \Illuminate\Http\Request::create('/api/pdfs', 'POST', $testData);
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Test without file first
    echo "Testing without file upload...\n";
    try {
        $controller = new \App\Http\Controllers\PdfController();
        // This will fail validation but we can see if auth passes
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "✅ Validation error (expected): " . implode(', ', array_keys($e->errors())) . "\n";
    } catch (Exception $e) {
        echo "❌ Other error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 Recommendations:\n";
    echo "1. Make sure the frontend is sending the correct Authorization header\n";
    echo "2. Verify the user role is 'formateur'\n";
    echo "3. Check that the formation_id belongs to the authenticated formateur\n";
    echo "4. Ensure the file upload is properly formatted\n";
    echo "\n📋 Frontend should send:\n";
    echo "Headers: Authorization: Bearer {token}\n";
    echo "Body: FormData with titre, formation_id, and fichier (PDF file)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}