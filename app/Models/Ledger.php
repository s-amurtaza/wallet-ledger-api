<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    public $timestamps = false;

    protected $table = 'ledger';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(
            WalletAccount::class,
            'user_id',
            'user_id'
        );
    }
}
