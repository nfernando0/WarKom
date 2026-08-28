<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Community;
use App\Models\User;
use App\Models\Transaction;

Route::get('/', function () {
    $categories = Category::withCount('listings')->orderBy('name')->get();
    $featuredListings = Listing::with(['images', 'category', 'community', 'creator'])
        ->where('status', 'tersedia')
        ->latest()
        ->take(8)
        ->get();
    $communities = Community::withCount('members')->take(6)->get();
    $stats = [
        'listings' => Listing::count(),
        'communities' => Community::count(),
        'users' => User::count(),
        'transactions' => Transaction::where('status', 'selesai')->count(),
    ];

    return view('welcome', compact('categories', 'featuredListings', 'communities', 'stats'));
})->name('home');

use App\Models\Conversation;

Route::get('marketplace', App\Livewire\Public\Marketplace::class)->name('public.marketplace');
Route::get('marketplace/{listing}', App\Livewire\Public\ListingDetail::class)->name('public.listing.show');
Route::get('start-chat/{listing}', function (Listing $listing) {
    if (! auth()->check()) {
        session()->flash('info', 'Silakan masuk terlebih dahulu untuk mengirim pesan ke penjual.');
        return redirect()->guest(route('login'));
    }

    $user = auth()->user();
    if ($listing->user_id === $user->id) {
        session()->flash('info', 'Ini adalah barang yang Anda jual sendiri.');
        return redirect()->route('public.listing.show', $listing);
    }

    $conversation = Conversation::firstOrCreate(
        [
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
        ],
        [
            'seller_id' => $listing->user_id,
        ]
    );

    return redirect()->route('chat.index', ['conversation' => $conversation->id]);
})->name('public.start-chat');

Route::get('komunitas', App\Livewire\Public\CommunityDirectory::class)->name('public.community');
Route::get('kategori', App\Livewire\Public\CategoryDirectory::class)->name('public.category');
Route::middleware(['auth', 'verified'])->get('chat/{conversation?}', App\Livewire\Chat\Index::class)->name('chat.index');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');

    Route::get('community', App\Livewire\Community\Index::class)->name('community.index');
    Route::get('community/create', App\Livewire\Community\Create::class)->name('community.create');

    Route::get('categories', App\Livewire\Category\Index::class)->name('category.index');

    Route::get('listings', App\Livewire\Listing\Index::class)->name('listing.index');
    Route::get('listings/create', App\Livewire\Listing\Create::class)->name('listing.create');
    Route::get('listings/{listing}', App\Livewire\Listing\Show::class)->name('listing.show');
    Route::get('listings/{listing}/edit', App\Livewire\Listing\Edit::class)->name('listing.edit');

    Route::get('transactions', App\Livewire\Transaction\Index::class)->name('transaction.index');
});

require __DIR__.'/settings.php';
