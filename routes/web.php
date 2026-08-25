<?php

use App\Http\Middleware\EnsureAccessTokenIsValid;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::auth-code')->name('home');

Route::middleware(EnsureAccessTokenIsValid::class)->group(function () {
    Route::livewire('/livros', 'pages::books')->name('books');
    Route::livewire('/livros/{book}', 'pages::book-show')->name('books.show');
    Route::livewire('/estudar/{chapter}', 'pages::study')->name('study');
    Route::livewire('/estudar/{chapter}/resultados', 'pages::study-results')->name('study.results');

    Route::post('/sair', function () {
        session()->forget('access_token_id');

        return redirect()->route('home');
    })->name('logout');
});
