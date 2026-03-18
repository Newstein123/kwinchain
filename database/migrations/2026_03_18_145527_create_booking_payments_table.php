<?php

use App\Enums\BookingPaymentStatus;
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
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('payment_type');
            $table->unsignedTinyInteger('payment_method');
            $table->decimal('amount', 10, 2);
            $table->string('proof_image', 255)->nullable();

            $table->unsignedTinyInteger('status')
                ->default(BookingPaymentStatus::Pending->value);

            $table->string('transaction_ref', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
