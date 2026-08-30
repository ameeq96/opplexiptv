<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('marketing_email_consented_at')->nullable();
            $table->timestamp('marketing_email_opted_out_at')->nullable();
            $table->timestamp('marketing_whatsapp_consented_at')->nullable();
            $table->timestamp('marketing_whatsapp_opted_out_at')->nullable();
            $table->timestamp('marketing_ads_consented_at')->nullable();
            $table->timestamp('marketing_ads_opted_out_at')->nullable();
            $table->string('marketing_consent_version', 20)->nullable();
            $table->string('marketing_consent_source', 50)->nullable();
            $table->string('marketing_consent_locale', 10)->nullable();
            $table->string('marketing_consent_ip_hash', 64)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unknown');
            $table->timestamp('paid_at')->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('paid_currency', 3)->nullable();
            $table->string('payment_provider', 60)->nullable();
            $table->string('provider_transaction_id', 191)->nullable();
            $table->string('ga_client_id', 100)->nullable();
            $table->timestamp('analytics_consented_at')->nullable();
            $table->timestamp('ga_purchase_processing_at')->nullable();
            $table->uuid('ga_purchase_processing_token')->nullable();
            $table->timestamp('ga_purchase_sent_at')->nullable();
            $table->string('locale', 10)->default('en');
            $table->unique(
                ['payment_provider', 'provider_transaction_id'],
                'orders_payment_provider_transaction_unique'
            );
            $table->index(['payment_status', 'ga_purchase_sent_at'], 'orders_ga_purchase_dispatch_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_payment_provider_transaction_unique');
            $table->dropIndex('orders_ga_purchase_dispatch_index');
            $table->dropColumn([
                'payment_status',
                'paid_at',
                'paid_amount',
                'paid_currency',
                'payment_provider',
                'provider_transaction_id',
                'ga_client_id',
                'analytics_consented_at',
                'ga_purchase_processing_at',
                'ga_purchase_processing_token',
                'ga_purchase_sent_at',
                'locale',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'marketing_email_consented_at',
                'marketing_email_opted_out_at',
                'marketing_whatsapp_consented_at',
                'marketing_whatsapp_opted_out_at',
                'marketing_ads_consented_at',
                'marketing_ads_opted_out_at',
                'marketing_consent_version',
                'marketing_consent_source',
                'marketing_consent_locale',
                'marketing_consent_ip_hash',
            ]);
        });
    }
};
