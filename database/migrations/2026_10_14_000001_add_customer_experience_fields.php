<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->dateTime('trial_started_at')->nullable();
            $table->dateTime('trial_expires_at')->nullable();
            $table->dateTime('trial_owner_verified_at')->nullable();
            $table->unsignedBigInteger('trial_activated_by_admin_id')->nullable();
            $table->unsignedBigInteger('trial_package_id')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('renewal_email_enabled')->nullable();
            $table->dateTime('renewal_email_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->dropColumn([
                'trial_started_at', 'trial_expires_at', 'trial_owner_verified_at', 'trial_activated_by_admin_id', 'trial_package_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['renewal_email_enabled', 'renewal_email_updated_at']);
        });
    }
};
