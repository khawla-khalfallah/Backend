<?php
// Test PDF creation with HTTP simulation

echo "🌐 Testing PDF Upload via HTTP Simulation\n";
echo "========================================\n\n";

$baseUrl = 'http://localhost:8000';
$apiUrl = $baseUrl . '/api';

// Step 1: Login to get token
echo "🔐 Step 1: Login to get authentication token\n";

$loginData = [
    'email' => 'test@email.com', // Use the formateur we found
    'password' => 'password123' // You might need to adjust this
];

$loginResponse = makeRequest($apiUrl . '/login', 'POST', $loginData);

if ($loginResponse['http_code'] !== 200) {
    echo "❌ Login failed with code: " . $loginResponse['http_code'] . "\n";
    echo "Response: " . $loginResponse['response'] . "\n";
    exit;
}

$loginResult = json_decode($loginResponse['response'], true);
if (!isset($loginResult['token'])) {
    echo "❌ No token in login response\n";
    echo "Response: " . $loginResponse['response'] . "\n";
    exit;
}

$token = $loginResult['token'];
echo "✅ Login successful, token: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Test PDF upload
echo "📤 Step 2: Attempt PDF upload\n";

// Create a temporary PDF file for testing
$tempPdfContent = "%PDF-1.4\n1 0 obj\n<</Type/Catalog/Pages 2 0 R>>\nendobj\n2 0 obj\n<</Type/Pages/Kids[3 0 R]/Count 1>>\nendobj\n3 0 obj\n<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000074 00000 n \n0000000120 00000 n \ntrailer\n<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";
$tempFile = tempnam(sys_get_temp_dir(), 'test_pdf_') . '.pdf';
file_put_contents($tempFile, $tempPdfContent);

echo "📄 Created temporary PDF: " . $tempFile . "\n";

// Prepare multipart form data
$pdfData = [
    'titre' => 'Test PDF Upload',
    'formation_id' => '1', // Use the formation we found
    'fichier' => new CURLFile($tempFile, 'application/pdf', 'test.pdf')
];

$pdfResponse = makeMultipartRequest($apiUrl . '/pdfs', 'POST', $pdfData, $token);

echo "HTTP Code: " . $pdfResponse['http_code'] . "\n";
echo "Response: " . $pdfResponse['response'] . "\n";

// Cleanup
unlink($tempFile);

if ($pdfResponse['http_code'] === 403) {
    echo "\n🚨 403 Forbidden Error Detected!\n";
    echo "This could be due to:\n";
    echo "1. ❌ User not authenticated properly\n";
    echo "2. ❌ User doesn't have 'formateur' role\n";
    echo "3. ❌ Formation doesn't belong to this formateur\n";
    echo "4. ❌ Formateur profile status is not 'accepte'\n";
    echo "5. ❌ Middleware blocking the request\n";
    
    // Let's check user info
    echo "\n🔍 Checking user profile...\n";
    $profileResponse = makeRequest($apiUrl . '/profile', 'GET', null, $token);
    echo "Profile response: " . $profileResponse['response'] . "\n";
    
} elseif ($pdfResponse['http_code'] === 201) {
    echo "\n✅ PDF upload successful!\n";
} else {
    echo "\n⚠️ Unexpected response code: " . $pdfResponse['http_code'] . "\n";
}

function makeRequest($url, $method, $data, $token = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = ['Accept: application/json'];
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }
    }
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0, 'response' => ''];
    }
    
    return [
        'response' => $response,
        'http_code' => $httpCode
    ];
}

function makeMultipartRequest($url, $method, $data, $token = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0, 'response' => ''];
    }
    
    return [
        'response' => $response,
        'http_code' => $httpCode
    ];
}