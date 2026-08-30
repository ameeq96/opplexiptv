<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vendor', 30)->nullable();
            $table->string('connection_name', 100)->nullable();
            $table->decimal('connection_price', 10, 2)->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('locale', 10)->default('en');
            $table->timestamp('email_consented_at')->nullable();
            $table->timestamp('whatsapp_consented_at')->nullable();
            $table->timestamp('ads_consented_at')->nullable();
            $table->string('consent_version', 20)->nullable();
            $table->string('consent_ip_hash', 64)->nullable();
            $table->string('referral_code', 40)->nullable();
            $table->timestamp('last_activity_at')->index();
            $table->timestamp('retention_expires_at')->index();
            $table->foreignId('completed_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_drafts');
    }
};
