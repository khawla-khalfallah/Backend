<?php
// Test script for user registration API

echo "🔥 TESTING REGISTRATION API 🔥\n";
echo "===============================\n\n";

$baseUrl = 'http://localhost:8000';
$registerUrl = $baseUrl . '/api/users';

// Test cases
$testCases = [
    [
        'name' => '✅ Valid Apprenant Registration',
        'data' => [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont' . time() . '@test.com',
            'password' => 'Test123456!',
            'password_confirmation' => 'Test123456!',
            'role' => 'apprenant',
            'niveau_etude' => 'Master en Informatique'
        ],
        'shouldSucceed' => true
    ],
    [
        'name' => '✅ Valid Formateur Registration',
        'data' => [
            'nom' => 'Martin',
            'prenom' => 'Marie',
            'email' => 'marie.martin' . time() . '@test.com',
            'password' => 'Test123456!',
            'password_confirmation' => 'Test123456!',
            'role' => 'formateur',
            'specialite' => 'Développement Web',
            'bio' => 'Développeur full-stack avec 10 ans d\'expérience'
        ],
        'shouldSucceed' => true
    ],
    [
        'name' => '✅ Valid Recruteur Registration',
        'data' => [
            'nom' => 'Smith',
            'prenom' => 'John',
            'email' => 'john.smith' . time() . '@test.com',
            'password' => 'Test123456!',
            'password_confirmation' => 'Test123456!',
            'role' => 'recruteur',
            'entreprise' => 'TechCorp Inc.'
        ],
        'shouldSucceed' => true
    ],
    [
        'name' => '❌ Invalid Email Format',
        'data' => [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'invalid-email',
            'password' => 'Test123456!',
            'password_confirmation' => 'Test123456!',
            'role' => 'apprenant',
            'niveau_etude' => 'Bachelor'
        ],
        'shouldSucceed' => false
    ],
    [
        'name' => '❌ Weak Password',
        'data' => [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test.weak' . time() . '@test.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'apprenant',
            'niveau_etude' => 'Bachelor'
        ],
        'shouldSucceed' => false
    ],
    [
        'name' => '❌ Password Mismatch',
        'data' => [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test.mismatch' . time() . '@test.com',
            'password' => 'Test123456!',
            'password_confirmation' => 'Different123!',
            'role' => 'apprenant',
            'niveau_etude' => 'Bachelor'
        ],
        'shouldSucceed' => false
    ],
    [
        'name' => '❌ Missing Required Field for Apprenant',
        'data' => [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test.missing' . time() . '@test.com',
            'password' => 'Test123456!',
            'password_confirmation' => 'Test123456!',
            'role' => 'apprenant'
            // Missing niveau_etude
        ],
        'shouldSucceed' => false
    ],
    [
        'name' => '❌ Invalid Role',
        'data' => [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test.invalidrole' . time() . '@test.com',
            'password' => 'Test123456!',
            'password_confirmation' => 'Test123456!',
            'role' => 'invalid_role'
        ],
        'shouldSucceed' => false
    ]
];

// Function to make HTTP request
function makeRequest($url, $data) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Run tests
$passed = 0;
$failed = 0;

foreach ($testCases as $i => $test) {
    echo "Test " . ($i + 1) . ": " . $test['name'] . "\n";
    echo str_repeat('-', 50) . "\n";
    
    $result = makeRequest($registerUrl, $test['data']);
    
    if (isset($result['error'])) {
        echo "❌ CURL Error: " . $result['error'] . "\n\n";
        $failed++;
        continue;
    }
    
    $success = ($result['http_code'] >= 200 && $result['http_code'] < 300);
    $expectedResult = $test['shouldSucceed'];
    
    echo "HTTP Code: " . $result['http_code'] . "\n";
    echo "Expected Success: " . ($expectedResult ? 'Yes' : 'No') . "\n";
    echo "Actual Success: " . ($success ? 'Yes' : 'No') . "\n";
    
    if ($success && isset($result['data']['user'])) {
        echo "✅ User Created:\n";
        echo "  - ID: " . $result['data']['user']['id'] . "\n";
        echo "  - Email: " . $result['data']['user']['email'] . "\n";
        echo "  - Role: " . $result['data']['user']['role'] . "\n";
        echo "  - Token: " . (isset($result['data']['token']) ? 'Generated' : 'Missing') . "\n";
    } else {
        echo "Response: " . $result['response'] . "\n";
    }
    
    // Check if test passed
    if ($success === $expectedResult) {
        echo "🎉 TEST PASSED\n";
        $passed++;
    } else {
        echo "💥 TEST FAILED - Expected " . ($expectedResult ? 'success' : 'failure') . 
             " but got " . ($success ? 'success' : 'failure') . "\n";
        $failed++;
    }
    
    echo "\n" . str_repeat('=', 60) . "\n\n";
}

// Summary
echo "📊 TEST SUMMARY\n";
echo "================\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "📈 Success Rate: " . round(($passed / ($passed + $failed)) * 100, 2) . "%\n";

if ($failed === 0) {
    echo "\n🎉 ALL TESTS PASSED! Registration API is working correctly! 🎉\n";
} else {
    echo "\n⚠️  Some tests failed. Check the implementation.\n";
}