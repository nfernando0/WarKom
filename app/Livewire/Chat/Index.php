<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

#[Layout('layouts.public')]
class Index extends Component
{
    public ?int $activeConversationId = null;
    public string $filter = 'all'; // 'all', 'buying', 'selling'
    public string $search = '';
    public string $messageText = '';

    protected $queryString = [
        'activeConversationId' => ['as' => 'c', 'except' => null],
        'filter' => ['except' => 'all'],
    ];

    public function mount(?int $conversation = null): void
    {
        $userId = auth()->id();

        if ($conversation) {
            $conv = Conversation::where('id', $conversation)
                ->where(function ($q) use ($userId) {
                    $q->where('buyer_id', $userId)
                      ->orWhere('seller_id', $userId);
                })
                ->first();

            if ($conv) {
                $this->activeConversationId = $conv->id;
                $this->markActiveAsRead();
                return;
            }
        }

        // If no explicit conversation passed in route parameter, check if one was provided in query string or pick latest
        if (! $this->activeConversationId) {
            $latest = Conversation::where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            })
            ->latest('updated_at')
            ->first();

            if ($latest) {
                $this->activeConversationId = $latest->id;
                $this->markActiveAsRead();
            }
        } else {
            $this->markActiveAsRead();
        }
    }

    public function selectConversation(int $id): void
    {
        $userId = auth()->id();
        $conv = Conversation::where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            })
            ->first();

        if ($conv) {
            $this->activeConversationId = $conv->id;
            $this->messageText = '';
            $this->markActiveAsRead();
            $this->dispatch('chat-switched');
        }
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'buying', 'selling'], true)) {
            $this->filter = $filter;
        }
    }

    public function sendMessage(): void
    {
        $this->messageText = trim($this->messageText);

        if (empty($this->messageText)) {
            return;
        }

        $this->validate([
            'messageText' => 'required|string|max:2000',
        ]);

        $userId = auth()->id();
        $conversation = Conversation::where('id', $this->activeConversationId)
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            })
            ->first();

        if (! $conversation) {
            return;
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $this->messageText,
            'read_at' => null,
        ]);

        $conversation->touch();

        $this->messageText = '';
        $this->dispatch('message-sent');
    }

    public function sendQuickMessage(string $text): void
    {
        $this->messageText = $text;
        $this->sendMessage();
    }

    public function createTransactionFromChat(): void
    {
        $userId = auth()->id();
        $conversation = Conversation::with('listing')->where('id', $this->activeConversationId)
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            })
            ->first();

        if (! $conversation || ! $conversation->listing) {
            return;
        }

        // Check if there is already a pending transaction
        $existing = Transaction::where('listing_id', $conversation->listing_id)
            ->where('buyer_id', $conversation->buyer_id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $this->redirectRoute('transaction.index');
            return;
        }

        $transaction = Transaction::create([
            'listing_id' => $conversation->listing_id,
            'buyer_id' => $conversation->buyer_id,
            'seller_id' => $conversation->seller_id,
            'price' => $conversation->listing->price,
            'status' => 'pending',
        ]);

        $conversation->listing->update(['status' => 'ditahan']);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => "📦 Transaksi baru telah disepakati untuk barang ini senilai Rp " . number_format($conversation->listing->price, 0, ',', '.') . " (Status: Menunggu Penyelesaian).",
            'read_at' => null,
        ]);
        $conversation->touch();

        session()->flash('success', 'Transaksi berhasil dibuat! Silakan pantau status di halaman Transaksi.');
        $this->redirectRoute('transaction.index');
    }

    public function markActiveAsRead(): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        $userId = auth()->id();

        Message::where('conversation_id', $this->activeConversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }

    public function render()
    {
        $userId = auth()->id();

        // Automatically mark messages as read on each poll/render if viewing active conversation
        $this->markActiveAsRead();

        // Query conversations for the user
        $conversationsQuery = Conversation::with([
            'listing' => fn($q) => $q->with('images'),
            'buyer',
            'seller',
            'latestMessage',
        ])
        ->where(function ($q) use ($userId) {
            if ($this->filter === 'buying') {
                $q->where('buyer_id', $userId);
            } elseif ($this->filter === 'selling') {
                $q->where('seller_id', $userId);
            } else {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            }
        })
        ->latest('updated_at');

        $allConversations = $conversationsQuery->get();

        // Apply search filter if needed
        if (! empty($this->search)) {
            $searchTerm = mb_strtolower($this->search);
            $allConversations = $allConversations->filter(function ($conv) use ($userId, $searchTerm) {
                $otherUser = $conv->getOtherUser($userId);
                $partnerName = mb_strtolower($otherUser?->name ?? '');
                $listingTitle = mb_strtolower($conv->listing?->title ?? '');

                return str_contains($partnerName, $searchTerm) || str_contains($listingTitle, $searchTerm);
            });
        }

        // Active conversation and its messages
        $activeConversation = null;
        $messages = collect();

        if ($this->activeConversationId) {
            $activeConversation = Conversation::with([
                'listing' => fn($q) => $q->with(['images', 'creator', 'community', 'category']),
                'buyer',
                'seller',
            ])
            ->where('id', $this->activeConversationId)
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            })
            ->first();

            if ($activeConversation) {
                $messages = Message::with('sender')
                    ->where('conversation_id', $activeConversation->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        return view('livewire.chat.index', [
            'conversations' => $allConversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'currentUserId' => $userId,
        ])->title('Pesan & Chat - WarKom');
    }
}
