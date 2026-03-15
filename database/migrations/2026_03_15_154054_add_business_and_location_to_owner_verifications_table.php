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
        Schema::table('owner_verifications', function (Blueprint $table): void {
            $table->string('business_name', 255)->nullable()->after('selfie_with_id');
            $table->string('utility_bill_image', 255)->nullable()->after('land_document_image');
            $table->decimal('field_location_lat', 10, 8)->nullable()->after('utility_bill_image');
            $table->decimal('field_location_lng', 11, 8)->nullable()->after('field_location_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_verifications', function (Blueprint $table): void {
            $table->dropColumn(['business_name', 'utility_bill_image', 'field_location_lat', 'field_location_lng']);
        });
    }
};
