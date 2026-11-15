<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditRequest;
use App\Http\Requests\DebitRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Credit wallet.
     */
    public function credit(CreditRequest $request): JsonResponse
    {
        $data = $request->validated();

        $wallet = $this->walletService->credit(
            $data['user_id'],
            (int) $data['amount']
        );

        return response()->json([
            'user_id' => $wallet->user_id,
            'balance' => $wallet->balance,
        ], 200);
    }

    /**
     * Debit wallet.
     */
    public function debit(DebitRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $wallet = $this->walletService->debit(
                $data['user_id'],
                (int) $data['amount']
            );

            return response()->json([
                'user_id' => $wallet->user_id,
                'balance' => $wallet->balance,
            ], 200);

        } catch (RuntimeException $e) {

            if ($e->getMessage() === 'user_not_found') {
                return response()->json([
                    'error' => 'user_not_found',
                    'message' => 'User wallet does not exist',
                ], 404);
            }

            if ($e->getMessage() === 'insufficient_funds') {
                return response()->json([
                    'error' => 'insufficient_funds',
                    'message' => 'Insufficient wallet balance',
                ], 409);
            }

            throw $e;
        }
    }

    /**
     * Get wallet balance.
     */
    public function balance(string $user_id): JsonResponse
    {
        if (!preg_match('/\A[A-Za-z0-9_-]{1,64}\z/', $user_id)) {
            return response()->json([
                'error' => 'invalid_request',
                'message' => 'Invalid user_id',
            ], 400);
        }

        $wallet = $this->walletService->getBalance($user_id);

        if (!$wallet) {
            return response()->json([
                'error' => 'user_not_found',
                'message' => 'User wallet does not exist',
            ], 404);
        }

        return response()->json([
            'user_id' => $wallet->user_id,
            'balance' => $wallet->balance,
        ], 200);
    }
}
