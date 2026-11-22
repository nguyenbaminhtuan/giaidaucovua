<?php

use App\Livewire\Tournament\SetupWizard;
use App\Livewire\Tournament\TournamentDashboard;
use App\Livewire\Tournament\TournamentList;
use Illuminate\Support\Facades\Route;

Route::get('/', TournamentList::class)->name('home');
Route::get('/tournaments', TournamentList::class)->name('tournament.index');
Route::get('/tournaments/create', SetupWizard::class)->name('tournament.create');
Route::get('/tournaments/{tournament}', TournamentDashboard::class)->name('tournament.show');
