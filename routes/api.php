<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\RecruteurController;
use App\Http\Controllers\AdministrateurController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\InscritController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DailyController;
use App\Http\Controllers\EmailController;


// Routes publiques
Route::post('/login', [AuthController::class, 'login']);
Route::get('/formations', [FormationController::class, 'index']);


// Route publique pour rechercher un apprenant (pas besoin d’être authentifié)
Route::get('/apprenants/search', [ApprenantController::class, 'search']);
// Route publique pour envoyer un email
Route::post('/send-email', [EmailController::class, 'send']);
// Route publique pour inscription
Route::post('/users', [UserController::class, 'store']);


// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Profile utilisateur
    Route::get('/profile', [UserController::class, 'me']);
     Route::get('/formations/ranking/bayesian', [FormationController::class, 'getBayesianRanking']);
   //  Route::get('/formations/ranking/bayesian', [FormationController::class, 'getBayesianRanking']);
    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // Apprenants
    Route::prefix('apprenants')->group(function () {
        Route::get('/', [ApprenantController::class, 'index']);
        Route::post('/', [ApprenantController::class, 'store']);
        Route::get('/{id}', [ApprenantController::class, 'show']);
        Route::put('/{id}', [ApprenantController::class, 'update']);
        Route::delete('/{id}', [ApprenantController::class, 'destroy']);
        Route::get('/{id}/formations', [InscritController::class, 'getFormationsByApprenant']);
        Route::get('/chercher-apprenant', [ApprenantController::class, 'chercherParNom']);
    });

    // Formateurs
    Route::prefix('formateurs')->group(function () {
        Route::get('/', [FormateurController::class, 'index']);
        Route::post('/', [FormateurController::class, 'store']);
        Route::get('/{id}', [FormateurController::class, 'show']);
        Route::put('/{id}', [FormateurController::class, 'update']);
        Route::delete('/{id}', [FormateurController::class, 'destroy']);
    });

    // Recruteurs
    Route::prefix('recruteurs')->group(function () {
        Route::get('/', [RecruteurController::class, 'index']);
        Route::post('/', [RecruteurController::class, 'store']);
        Route::get('/{id}', [RecruteurController::class, 'show']);
        Route::put('/{id}', [RecruteurController::class, 'update']);
        Route::delete('/{id}', [RecruteurController::class, 'destroy']);
    });

    // Formations
    Route::prefix('formations')->group(function () {
        Route::post('/', [FormationController::class, 'store']);
        Route::get('/{id}', [FormationController::class, 'show']);
        Route::put('/{id}', [FormationController::class, 'update']);
        Route::delete('/{id}', [FormationController::class, 'destroy']);
        Route::get('/chercher-formation', [FormationController::class, 'chercherParTitre']);
        Route::get('/{id}/apprenants', [FormationController::class, 'getApprenants']);
        
        // Évaluations
        Route::post('/{formation}/evaluations', [EvaluationController::class, 'evaluer']);
        Route::get('/{formation}/evaluations/mon-evaluation', [EvaluationController::class, 'getEvaluation']);
    });

    // Inscriptions
    Route::prefix('inscrits')->group(function () {
        Route::get('/', [InscritController::class, 'index']);
        Route::post('/', [InscritController::class, 'store']);
        Route::get('/{id}', [InscritController::class, 'show']);
        Route::put('/{id}', [InscritController::class, 'update']);
        Route::delete('/{id}', [InscritController::class, 'destroy']);
        Route::get('/apprenant/{id}', [InscritController::class, 'getByApprenant']);
    });


    // Ressources API
    Route::apiResource('certificats', CertificatController::class);
    Route::apiResource('examens', ExamenController::class);
    Route::post('/examens/{id}/soumettre', [ExamenController::class, 'soumettre']);
    Route::apiResource('pdfs', PdfController::class);
    Route::apiResource('seances', SeanceController::class);
    Route::apiResource('videos', VideoController::class);
    Route::apiResource('administrateurs', AdministrateurController::class);
    Route::apiResource('questions', QuestionController::class);
});
Route::post('/create-room', [DailyController::class, 'createRoom']);
Route::post('/create-room', [RoomController::class, 'createRoom']);

// Routes de test (peuvent être protégées ou non selon les besoins)
Route::middleware(['auth:sanctum', 'log.requests'])->group(function () {
    Route::get('/tables', [TestController::class, 'listTables']);
    Route::get('/primary-keys', [TestController::class, 'listPrimaryKeys']);
});

Route::prefix('formations')->group(function () {
    // ... routes existantes ...
    Route::get('/ranking/bayesian', [FormationController::class, 'getBayesianRanking']);
});
Route::get('/formations/global-average', [FormationController::class, 'getGlobalAverage']);
Route::get('/formations/ranked', [FormationController::class, 'getRankedCourses']);
// Ajoutez ces nouvelles routes
Route::get('/formations/with-stats', [FormationController::class, 'getCoursesWithStats']);


Route::apiResource('certificats', CertificatController::class);
Route::apiResource('examens', ExamenController::class);
Route::post('/examens/{id}/soumettre', [ExamenController::class, 'soumettre']);

Route::apiResource('pdfs', PdfController::class);
Route::apiResource('seances', SeanceController::class);
Route::apiResource('videos', VideoController::class);
Route::apiResource('administrateurs', AdministrateurController::class);
Route::apiResource('questions', QuestionController::class);
Route::post('/avis', [AvisController::class, 'store']);




Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/profile', [UserController::class, 'me']);
Route::get('/apprenants/{id}/formations', [InscritController::class, 'getFormationsByApprenant']);

















Route::get('/tables', [TestController::class, 'listTables']);
Route::get('/primary-keys', [TestController::class, 'listPrimaryKeys']);
Route::get('/chercher-apprenant', [ApprenantController::class, 'chercherParNom']);
Route::get('/chercher-formation', [FormationController::class, 'chercherParTitre']);
Route::get('/formations/{id}/apprenants', [FormationController::class, 'getApprenants']);
