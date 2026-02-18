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
        Schema::create('owner_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->string('nrc_number', 50)->nullable();
            $table->string('nrc_front_image', 255)->nullable();
            $table->string('nrc_back_image', 255)->nullable();
            $table->string('selfie_with_id', 255)->nullable();
            $table->string('business_license_image', 255)->nullable();
            $table->string('land_document_image', 255)->nullable();
            $table->unsignedTinyInteger('status')->default(\App\Enums\OwnerVerificationStatus::Pending->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_verifications');
    }
};
