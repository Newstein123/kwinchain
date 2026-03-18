<?php

use App\Enums\BookingPaymentRequirement;
use App\Enums\BookingStatus;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained()->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('payment_requirement')
                ->default(BookingPaymentRequirement::Deposit->value);

            $table->unsignedTinyInteger('status')
                ->default(BookingStatus::Pending->value);

            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('reward_amount', 10, 2)->nullable();

            $table->decimal('total_price', 10, 2);
            $table->decimal('paid_amount', 10, 2);

            $table->string('qr_token', 100)->unique();
            $table->timestamp('qr_generated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
