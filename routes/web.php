<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('community', App\Livewire\Community\Index::class)->name('community.index');
    Route::get('community/create', App\Livewire\Community\Create::class)->name('community.create');

    Route::get('listings', App\Livewire\Listing\Index::class)->name('listing.index');
    Route::get('listings/create', App\Livewire\Listing\Create::class)->name('listing.create');
    Route::get('listings/{listing}', App\Livewire\Listing\Show::class)->name('listing.show');
    Route::get('listings/{listing}/edit', App\Livewire\Listing\Edit::class)->name('listing.edit');

    Route::get('chat/{conversation?}', App\Livewire\Chat\Index::class)->name('chat.index');
    Route::get('transactions', App\Livewire\Transaction\Index::class)->name('transaction.index');
});

require __DIR__.'/settings.php';
