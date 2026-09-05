<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTransactionRequest;
use App\Http\Requests\Api\SubmitReviewRequest;
use App\Http\Requests\Api\PaymentWebhookRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Listing;
use App\Models\Review;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of user's transactions.
     *
     * GET /api/v1/transactions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Transaction::with(['listing.images', 'listing.category', 'buyer.community', 'seller.community'])
            ->latest();

        // If not admin, restrict to user's own transactions
        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            });
        }

        // Filter by role: buying, selling
        if ($request->filled('role')) {
            if ($request->role === 'buying') {
                $query->where('buyer_id', $user->id);
            } elseif ($request->role === 'selling') {
                $query->where('seller_id', $user->id);
            }
        }

        // Filter by order status: pending, selesai, dibatalkan
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by payment_status: unpaid, pending, settlement, expired, failed, refunded
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by invoice, listing title, buyer/seller name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('listing', function ($lq) use ($search) {
                      $lq->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('buyer', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $transactions = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar transaksi berhasil dimuat.',
            'data' => TransactionResource::collection($transactions->items()),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Store a newly created transaction in storage (Checkout / Buy).
     *
     * POST /api/v1/transactions
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $user = $request->user();
        $listing = Listing::with('creator')->findOrFail($request->listing_id);

        // Validation 1: Prevent buying own item
        if ($listing->user_id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat membeli barang yang Anda jual sendiri.',
            ], 422);
        }

        // Validation 2: Ensure item is available
        if ($listing->status !== 'tersedia') {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang ini sedang tidak tersedia atau sudah dipesan.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $user, $listing) {
            // Generate unique invoice number: INV-YYYYMMDD-XXXXXX
            $date = date('Ymd');
            $random = strtoupper(Str::random(6));
            $invoiceNumber = "INV-{$date}-{$random}";

            while (Transaction::where('invoice_number', $invoiceNumber)->exists()) {
                $random = strtoupper(Str::random(6));
                $invoiceNumber = "INV-{$date}-{$random}";
            }

            $price = $listing->price;
            $adminFee = 0.00;
            $totalAmount = $price + $adminFee;

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'listing_id' => $listing->id,
                'buyer_id' => $user->id,
                'seller_id' => $listing->user_id,
                'price' => $price,
                'admin_fee' => $adminFee,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_channel' => $request->input('payment_channel', 'cod'),
                'expired_at' => now()->addDays(2),
            ]);

            // Mark listing as held / booked
            $listing->update(['status' => 'ditahan']);

            // Create or get conversation between buyer & seller
            $conversation = Conversation::firstOrCreate(
                [
                    'listing_id' => $listing->id,
                    'buyer_id' => $user->id,
                ],
                [
                    'seller_id' => $listing->user_id,
                ]
            );

            // Send automated system notification message in chat
            $channelName = strtoupper($request->input('payment_channel', 'COD / Bayar di Tempat'));
            $noteText = $request->filled('notes') ? " Catatan: \"{$request->notes}\"" : "";
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'body' => "Halo! Saya telah mengajukan pembelian untuk \"{$listing->title}\" (Invoice: {$invoiceNumber}) via {$channelName}. Total: Rp " . number_format($totalAmount, 0, ',', '.') . ".{$noteText}",
            ]);

            $conversation->touch();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibuat.',
                'data' => new TransactionResource($transaction->load(['listing.images', 'listing.category', 'buyer.community', 'seller.community'])),
            ], 201);
        });
    }

    /**
     * Display the specified transaction.
     *
     * GET /api/v1/transactions/{transaction}
     */
    public function show(Request $request, string $idOrInvoice): JsonResponse
    {
        $user = $request->user();

        // Search by numeric ID or invoice_number
        $transaction = is_numeric($idOrInvoice)
            ? Transaction::with(['listing.images', 'listing.category', 'buyer.community', 'seller.community'])->findOrFail($idOrInvoice)
            : Transaction::with(['listing.images', 'listing.category', 'buyer.community', 'seller.community'])->where('invoice_number', $idOrInvoice)->firstOrFail();

        // Authorize: Buyer, Seller, or Admin
        if (! $user->isAdmin() && $transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk melihat transaksi ini.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksi berhasil dimuat.',
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Initiate or simulate payment for the transaction.
     *
     * POST /api/v1/transactions/{transaction}/pay
     */
    public function pay(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        // Authorize: Buyer only
        if ($transaction->buyer_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pembeli yang dapat melakukan pembayaran untuk transaksi ini.',
            ], 403);
        }

        if ($transaction->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => "Transaksi berstatus {$transaction->status} dan tidak dapat dibayar lagi.",
            ], 422);
        }

        if ($transaction->payment_status === 'settlement') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini sudah lunas.',
            ], 422);
        }

        $channel = $request->input('payment_channel', $transaction->payment_channel ?: 'qris');
        $refId = 'GW-' . strtoupper(Str::random(12));
        $paymentToken = 'pay_tok_' . bin2hex(random_bytes(16));
        $paymentUrl = url("/payment/{$transaction->invoice_number}?token={$paymentToken}");

        $transaction->update([
            'payment_channel' => $channel,
            'gateway_reference_id' => $refId,
            'payment_token' => $paymentToken,
            'payment_url' => $paymentUrl,
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Instruksi pembayaran berhasil dibuat.',
            'data' => [
                'transaction' => new TransactionResource($transaction->load(['listing', 'buyer', 'seller'])),
                'payment' => [
                    'invoice_number' => $transaction->invoice_number,
                    'amount' => (float) $transaction->total_amount,
                    'channel' => $channel,
                    'gateway_reference_id' => $refId,
                    'payment_token' => $paymentToken,
                    'payment_url' => $paymentUrl,
                    'expired_at' => $transaction->expired_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Mark transaction as completed (Selesai).
     *
     * POST /api/v1/transactions/{transaction}/complete
     */
    public function complete(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        // Authorize: Buyer, Seller, or Admin
        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menyelesaikan transaksi ini.',
            ], 403);
        }

        if ($transaction->status === 'selesai') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini sudah diselesaikan sebelumnya.',
            ], 422);
        }

        if ($transaction->status === 'dibatalkan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi yang telah dibatalkan tidak dapat diselesaikan.',
            ], 422);
        }

        $transaction->update([
            'status' => 'selesai',
            'payment_status' => $transaction->payment_status === 'unpaid' ? 'settlement' : $transaction->payment_status,
            'completed_at' => now(),
            'paid_at' => $transaction->paid_at ?? now(),
        ]);

        // Permanently mark listing as sold
        $transaction->listing?->update(['status' => 'terjual']);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil diselesaikan.',
            'data' => new TransactionResource($transaction->load(['listing', 'buyer', 'seller', 'review'])),
        ]);
    }

    /**
     * Cancel the transaction (Batalkan).
     *
     * POST /api/v1/transactions/{transaction}/cancel
     */
    public function cancel(Request $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        // Authorize: Buyer, Seller, or Admin
        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk membatalkan transaksi ini.',
            ], 403);
        }

        if ($transaction->status === 'selesai') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi yang sudah selesai tidak dapat dibatalkan.',
            ], 422);
        }

        if ($transaction->status === 'dibatalkan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini sudah dibatalkan sebelumnya.',
            ], 422);
        }

        $newPaymentStatus = in_array($transaction->payment_status, ['settlement', 'pending'], true) ? 'refunded' : 'failed';

        $transaction->update([
            'status' => 'dibatalkan',
            'payment_status' => $newPaymentStatus,
        ]);

        // Restore listing back to available
        $transaction->listing?->update(['status' => 'tersedia']);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil dibatalkan dan ketersediaan barang telah dipulihkan.',
            'data' => new TransactionResource($transaction->load(['listing', 'buyer', 'seller'])),
        ]);
    }

    /**
     * Submit a rating review for the completed transaction.
     *
     * POST /api/v1/transactions/{transaction}/review
     */
    public function review(SubmitReviewRequest $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();

        // Authorize: Buyer or Seller
        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pihak yang terlibat dalam transaksi ini yang dapat memberikan ulasan.',
            ], 403);
        }

        if ($transaction->status !== 'selesai') {
            return response()->json([
                'status' => 'error',
                'message' => 'Ulasan hanya dapat diberikan setelah transaksi berhasil diselesaikan.',
            ], 422);
        }

        // Check if review already submitted by this user
        $existing = Review::where('transaction_id', $transaction->id)
            ->where('reviewer_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah memberikan ulasan untuk transaksi ini.',
                'data' => $existing,
            ], 422);
        }

        // Determine reviewed user: If buyer submits -> review seller; if seller submits -> review buyer
        $reviewedUserId = ($transaction->buyer_id === $user->id)
            ? $transaction->seller_id
            : $transaction->buyer_id;

        $review = Review::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $user->id,
            'reviewed_user_id' => $reviewedUserId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan dan penilaian bintang berhasil disimpan.',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toIso8601String(),
                'transaction' => new TransactionResource($transaction->load(['listing', 'buyer', 'seller', 'review'])),
            ],
        ], 201);
    }

    /**
     * Handle payment gateway webhook callback notifications.
     *
     * POST /api/v1/transactions/webhook
     */
    public function webhook(PaymentWebhookRequest $request): JsonResponse
    {
        $payload = $request->all();

        // Search by invoice_number or gateway_reference_id
        $query = Transaction::query();
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', $request->invoice_number);
        } elseif ($request->filled('gateway_reference_id')) {
            $query->where('gateway_reference_id', $request->gateway_reference_id);
        }

        $transaction = $query->first();

        if (! $transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan untuk notifikasi webhook ini.',
            ], 404);
        }

        $paymentStatus = $request->payment_status;
        $updates = [
            'payment_status' => $paymentStatus,
            'raw_response' => $payload,
        ];

        if ($request->filled('payment_channel')) {
            $updates['payment_channel'] = $request->payment_channel;
        }

        if ($paymentStatus === 'settlement') {
            $updates['paid_at'] = $request->filled('paid_at') ? Carbon::parse($request->paid_at) : now();
        }

        $transaction->update($updates);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi webhook pembayaran berhasil diproses.',
            'data' => [
                'invoice_number' => $transaction->invoice_number,
                'payment_status' => $transaction->payment_status,
                'paid_at' => $transaction->paid_at?->toIso8601String(),
            ],
        ]);
    }
}
