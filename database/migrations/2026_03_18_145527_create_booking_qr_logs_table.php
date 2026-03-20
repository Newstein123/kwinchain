<?php

use App\Enums\BookingQrScanStatus;
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
        Schema::create('booking_qr_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scanned_by')->constrained('owners')->cascadeOnDelete();
            $table->timestamp('scanned_at');
            $table->string('qr_token', 100);
            $table->string('ip_address', 45)->nullable();
            $table->string('device_info', 255)->nullable();
            $table->unsignedTinyInteger('scan_status')->default(BookingQrScanStatus::Invalid->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_qr_logs');
    }
};
