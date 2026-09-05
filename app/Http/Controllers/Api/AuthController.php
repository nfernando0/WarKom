<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate user and issue API Token.
     *
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with('community')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau kata sandi yang Anda masukkan salah.',
                'errors' => [
                    'email' => ['Kombinasi email dan kata sandi tidak cocok.'],
                ],
            ], 401);
        }

        $deviceName = $request->input('device_name') ?: ($request->header('User-Agent') ?: 'WarKom API Client');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'avatar' => $user->avatar,
                    'average_rating' => (float) $user->averageRating(),
                    'unread_messages' => $user->unreadMessagesCount(),
                    'pending_transactions' => $user->pendingTransactionsCount(),
                    'community' => $user->community ? [
                        'id' => $user->community->id,
                        'name' => $user->community->name,
                        'location' => $user->community->location,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Register a new user account and issue API Token.
     *
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => 'user',
            'community_id' => $request->community_id,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $deviceName = $request->input('device_name') ?: ($request->header('User-Agent') ?: 'WarKom API Client');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran akun berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'avatar' => $user->avatar,
                    'community' => $user->community ? [
                        'id' => $user->community->id,
                        'name' => $user->community->name,
                        'location' => $user->community->location,
                    ] : null,
                ],
            ],
        ], 201);
    }

    /**
     * Get authenticated user profile details.
     *
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('community');

        return response()->json([
            'status' => 'success',
            'message' => 'Data profil berhasil dimuat.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => $user->avatar,
                'average_rating' => (float) $user->averageRating(),
                'unread_messages' => $user->unreadMessagesCount(),
                'pending_transactions' => $user->pendingTransactionsCount(),
                'community' => $user->community ? [
                    'id' => $user->community->id,
                    'name' => $user->community->name,
                    'location' => $user->community->location,
                ] : null,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Revoke the current access token (Logout).
     *
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            // Revoke current Sanctum access token if available
            $currentToken = $user->currentAccessToken();
            if ($currentToken && method_exists($currentToken, 'delete')) {
                $currentToken->delete();
            }

            // Also clear web session if session auth was used
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dinonaktifkan.',
        ]);
    }
}
