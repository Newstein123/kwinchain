<?php

use App\Enums\OwnerVerificationLevel;
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
        Schema::table('owners', function (Blueprint $table): void {
            $table->unsignedTinyInteger('verification_level')->default(OwnerVerificationLevel::Basic->value)->after('status');
            $table->timestamp('email_verified_at')->nullable()->after('verification_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table): void {
            $table->dropColumn(['verification_level', 'email_verified_at']);
        });
    }
};
