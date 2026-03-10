<?php

use App\Enums\OwnerTrustedUserLevel;
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
        Schema::create('owner_trusted_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('trust_level')->default(OwnerTrustedUserLevel::Trusted->value);
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_trusted_users');
    }
};
