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
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('telegram_user_id')->unique()->nullable(false)->after('id');
            $table->string('telegram_username', 100)->nullable()->after('telegram_user_id');
            $table->string('phone', 20)->unique()->nullable()->after('name');
            $table->boolean('phone_verified')->default(false)->after('phone');
            $table->boolean('email_verified')->default(false)->after('email');
            $table->boolean('profile_completed')->default(false)->after('email_verified');
            $table->unsignedTinyInteger('status')->default(\App\Enums\UserStatus::Active->value)->after('profile_completed');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('password')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->dropColumn([
                'telegram_user_id',
                'telegram_username',
                'phone',
                'phone_verified',
                'email_verified',
                'profile_completed',
                'status',
                'last_login_at',
            ]);
            $table->string('password')->nullable(false)->change();
        });
    }
};
