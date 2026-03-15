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
        Schema::table('fields', function (Blueprint $table): void {
            $table->string('location', 100)->nullable()->after('description');
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('price_per_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table): void {
            $table->dropColumn(['location', 'deposit_amount']);
        });
    }
};
