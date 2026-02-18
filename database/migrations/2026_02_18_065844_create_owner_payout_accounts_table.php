<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('owner_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_name', 150)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('qr_code', 100)->nullable();
            $table->unsignedTinyInteger('status')->default(\App\Enums\OwnerPayoutAccountStatus::Pending->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_payout_accounts');
    }
};
