<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletAccount extends Model
{
    protected $table = 'wallet_accounts';

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
        ];
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(
            Ledger::class,
            'user_id',
            'user_id'
        );
    }
}
