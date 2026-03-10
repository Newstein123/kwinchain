<?php

use App\Enums\FieldStatus;
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
        Schema::create('fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedTinyInteger('surface_type');
            $table->unsignedSmallInteger('slot_duration');
            $table->decimal('price_per_hour', 10, 2);
            $table->unsignedTinyInteger('status')->default(FieldStatus::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
