<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/invitations/{token}', [ProjectInvitationController::class, 'show'])
    ->name('invitations.show');
Route::post('/invitations/{token}/accept', [ProjectInvitationController::class, 'accept'])
    ->middleware(['auth', 'verified'])
    ->name('invitations.accept');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/switch', [ProjectController::class, 'switch'])
        ->name('projects.switch');
    Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store'])
        ->name('projects.invitations.store');
    Route::delete('/projects/{project}/invitations/{invitation}', [ProjectInvitationController::class, 'destroy'])
        ->name('projects.invitations.destroy');
    Route::post('/projects/{project}/invitations/{invitation}/resend', [ProjectInvitationController::class, 'resend'])
        ->name('projects.invitations.resend');
});

require __DIR__.'/settings.php';
