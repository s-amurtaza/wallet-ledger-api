<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\WalletAccount;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function credit(string $userId, int $amount): WalletAccount
    {
        return DB::transaction(function () use ($userId, $amount) {

            WalletAccount::query()->insertOrIgnore([
                'user_id' => $userId,
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = WalletAccount::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $wallet->balance += $amount;
            $wallet->save();

            Ledger::create([
                'user_id' => $userId,
                'type' => 'credit',
                'amount' => $amount,
                'created_at' => now(),
            ]);

            return $wallet;
        }, 5);
    }


    public function debit(string $userId, int $amount): WalletAccount
    {
        return DB::transaction(function () use ($userId, $amount) {

            $wallet = WalletAccount::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            // User wallet does not exist
            if (!$wallet) {
                throw new RuntimeException('user_not_found');
            }

            // Check available balance
            if ($wallet->balance < $amount) {
                throw new RuntimeException('insufficient_funds');
            }

            // Deduct amount
            $wallet->balance -= $amount;
            $wallet->save();

            // Store debit transaction
            Ledger::create([
                'user_id' => $userId,
                'type' => 'debit',
                'amount' => $amount,
                'created_at' => now(),
            ]);

            return $wallet;
        }, 5);
    }


    public function getBalance(string $userId): ?WalletAccount
    {
        return WalletAccount::where('user_id', $userId)->first();
    }
}
