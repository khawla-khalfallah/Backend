<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'ExamenApprenant records: ' . App\Models\ExamenApprenant::count() . PHP_EOL;
echo 'ReponseApprenant records: ' . App\Models\ReponseApprenant::count() . PHP_EOL;

// Check the exam status for the test user
$user = App\Models\User::where('role', 'apprenant')->first();
if ($user) {
    $examenApprenant = App\Models\ExamenApprenant::where('apprenant_id', $user->id)->first();
    if ($examenApprenant) {
        echo "User has taken exam: ID " . $examenApprenant->examen_id . ", Note: " . $examenApprenant->note . ", Status: " . $examenApprenant->statut . PHP_EOL;
    } else {
        echo "User has not taken any exams" . PHP_EOL;
    }
}
