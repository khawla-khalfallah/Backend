<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/send-test-mail', function () {  
    Mail::to('khalfallahkhawlakh@gmail.com')->send(new TestMail());
    return 'Email envoyé avec succès !';
});
