<?php
// Check users and create test user if needed

echo "👥 User Management for PDF Test\n";
echo "==============================\n\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Check existing formateurs
    $formateurs = \App\Models\User::where('role', 'formateur')->with('formateur')->get();
    
    echo "📋 Existing Formateurs:\n";
    foreach ($formateurs as $user) {
        echo "- ID: {$user->id}, Email: {$user->email}, Status: {$user->formateur->status}\n";
    }
    
    // Check if we have an 'accepte' formateur
    $acceptedFormateur = \App\Models\User::where('role', 'formateur')
        ->whereHas('formateur', function($query) {
            $query->where('status', 'accepte');
        })
        ->first();
    
    if (!$acceptedFormateur) {
        echo "\n❌ No accepted formateur found. Creating test formateur...\n";
        
        // Create test formateur
        $testUser = \App\Models\User::create([
            'nom' => 'Test',
            'prenom' => 'Formateur',
            'email' => 'formateur.test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'formateur',
        ]);
        
        $testFormateur = \App\Models\Formateur::create([
            'user_id' => $testUser->id,
            'specialite' => 'Test Development',
            'bio' => 'Test formateur for PDF upload',
            'status' => 'accepte',
        ]);
        
        echo "✅ Created test formateur: {$testUser->email} / password123\n";
        $acceptedFormateur = $testUser;
    } else {
        echo "\n✅ Found accepted formateur: {$acceptedFormateur->email}\n";
    }
    
    // Check/create formations for this formateur
    $formations = \App\Models\Formation::where('formateur_id', $acceptedFormateur->id)->get();
    
    if ($formations->isEmpty()) {
        echo "📚 No formations found. Creating test formation...\n";
        
        $formation = \App\Models\Formation::create([
            'titre' => 'Formation Test PDF',
            'description' => 'Formation de test pour upload PDF',
            'duree' => 40,
            'formateur_id' => $acceptedFormateur->id,
            'prix' => 0,
        ]);
        
        echo "✅ Created test formation: {$formation->titre} (ID: {$formation->id})\n";
    } else {
        echo "📚 Found {$formations->count()} formation(s) for this formateur\n";
        $formation = $formations->first();
    }
    
    // Test login credentials
    echo "\n🔑 Testing login credentials...\n";
    
    if (\Illuminate\Support\Facades\Hash::check('password123', $acceptedFormateur->password)) {
        echo "✅ Password 'password123' is correct for {$acceptedFormateur->email}\n";
    } else {
        echo "❌ Password 'password123' is incorrect. Updating password...\n";
        $acceptedFormateur->password = \Illuminate\Support\Facades\Hash::make('password123');
        $acceptedFormateur->save();
        echo "✅ Password updated to 'password123'\n";
    }
    
    echo "\n📋 Test Credentials Ready:\n";
    echo "Email: {$acceptedFormateur->email}\n";
    echo "Password: password123\n";
    echo "Formation ID: {$formation->id}\n";
    echo "Formation Title: {$formation->titre}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}