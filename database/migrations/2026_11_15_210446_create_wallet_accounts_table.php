<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wallet_accounts')) {
            Schema::create('wallet_accounts', function (Blueprint $table) {
                $table->id();

                $table->string('user_id', 64)->unique();

                $table->bigInteger('balance')->default(0);

                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally do not drop existing wallet data.
    }
};
