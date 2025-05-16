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



use App\Http\Controllers\TestController;



Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index']);          // GET /api/users
    Route::post('/', [UserController::class, 'store']);         // POST /api/users
    Route::get('/{id}', [UserController::class, 'show']);       // GET /api/users/{id}
    Route::put('/{id}', [UserController::class, 'update']);     // PUT /api/users/{id}
    Route::delete('/{id}', [UserController::class, 'destroy']); // DELETE /api/users/{id}
});

Route::prefix('apprenants')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ApprenantController::class, 'index']);         // GET /api/apprenants
    Route::post('/', [ApprenantController::class, 'store']);        // POST /api/apprenants
    Route::get('/{id}', [ApprenantController::class, 'show']);      // GET /api/apprenants/{id}
    Route::put('/{id}', [ApprenantController::class, 'update']);    // PUT /api/apprenants/{id}
    Route::delete('/{id}', [ApprenantController::class, 'destroy']); // DELETE /api/apprenants/{id}
});

Route::prefix('formateurs')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [FormateurController::class, 'index']);           // GET /api/formateurs
    Route::post('/', [FormateurController::class, 'store']);          // POST /api/formateurs
    Route::get('/{id}', [FormateurController::class, 'show']);        // GET /api/formateurs/{id}
    Route::put('/{id}', [FormateurController::class, 'update']);      // PUT /api/formateurs/{id}
    Route::delete('/{id}', [FormateurController::class, 'destroy']);  // DELETE /api/formateurs/{id}
    
});


Route::prefix('recruteurs')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [RecruteurController::class, 'index']);           // GET /api/recruteurs
    Route::post('/', [RecruteurController::class, 'store']);          // POST /api/recruteurs
    Route::get('/{id}', [RecruteurController::class, 'show']);        // GET /api/recruteurs/{id}
    Route::put('/{id}', [RecruteurController::class, 'update']);      // PUT /api/recruteurs/{id}
    Route::delete('/{id}', [RecruteurController::class, 'destroy']);  // DELETE /api/recruteurs/{id}
    
});

Route::get('/formations', [FormationController::class, 'index']);

Route::prefix('formations')->middleware('auth:sanctum')->group(function () {
    // Route::get('/', [FormationController::class, 'index']);           // GET /api/formations
    Route::post('/', [FormationController::class, 'store']);          // POST /api/formations
    Route::get('/{id}', [FormationController::class, 'show']);        // GET /api/formations/{id}
    Route::put('/{id}', [FormationController::class, 'update']);      // PUT /api/formations/{id}
    Route::delete('/{id}', [FormationController::class, 'destroy']);  // DELETE /api/formations/{id}    
});


Route::prefix('inscrits')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [InscritController::class, 'index']);            // GET /api/inscrits
    Route::post('/', [InscritController::class, 'store']);           // POST /api/inscrits
    Route::get('/{id}', [InscritController::class, 'show']);         // GET /api/inscrits/{id}
    Route::delete('/{id}', [InscritController::class, 'destroy']);   // DELETE /api/inscrits/{id}
    Route::put('/{id}', [InscritController::class, 'update']); 

});

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


