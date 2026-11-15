<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ledger')) {
            Schema::create('ledger', function (Blueprint $table) {
                $table->id();

                $table->string('user_id', 64);

                $table->string('type', 10);

                $table->bigInteger('amount');

                $table->timestamp('created_at')->useCurrent();

                $table->index(
                    ['user_id', 'id'],
                    'idx_ledger_user_id_id'
                );
            });
        }
    }

    public function down(): void
    {
        // Intentionally do not drop existing transaction history.
    }
};
