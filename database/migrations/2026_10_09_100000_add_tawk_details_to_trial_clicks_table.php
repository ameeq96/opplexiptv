<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('trial_clicks', 'contact_email')) {
            Schema::table('trial_clicks', function (Blueprint $table) {
                $table->string('contact_email', 254)->nullable();
            });
        }
        if (!Schema::hasColumn('trial_clicks', 'message')) {
            Schema::table('trial_clicks', function (Blueprint $table) {
                $table->text('message')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'message']);
        });
    }
};
