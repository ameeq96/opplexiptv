<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->uuid('last_event_id')->nullable()->after('event_id');
            $table->string('contact_name', 120)->nullable()->after('last_event_id');
            $table->string('phone_normalized', 20)->nullable()->unique()->after('contact_name');
            $table->timestamp('whatsapp_contact_consented_at')->nullable()->after('phone_normalized');
            $table->string('consent_version', 32)->nullable()->after('whatsapp_contact_consented_at');
            $table->string('consent_source', 50)->nullable()->after('consent_version');
            $table->string('consent_locale', 10)->nullable()->after('consent_source');
            $table->char('consent_ip_hash', 64)->nullable()->after('consent_locale');
            $table->unsignedInteger('click_count')->default(1)->after('consent_ip_hash');
        });
    }

    public function down(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->dropUnique(['phone_normalized']);
            $table->dropColumn([
                'last_event_id',
                'contact_name',
                'phone_normalized',
                'whatsapp_contact_consented_at',
                'consent_version',
                'consent_source',
                'consent_locale',
                'consent_ip_hash',
                'click_count',
            ]);
        });
    }
};
