<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('source_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referred_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status', 20)->default('available');
            $table->string('reward_type', 30)->nullable();
            $table->decimal('reward_value', 10, 2)->nullable();
            $table->string('reward_currency', 3)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();
            $table->unique('source_order_id');
            $table->unique('referred_order_id');
        });

        Schema::create('marketing_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('dedupe_key', 191)->unique();
            $table->string('workflow', 30)->index();
            $table->string('stage', 30);
            $table->string('channel', 20);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checkout_draft_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained()->nullOnDelete();
            $table->string('locale', 10)->default('en');
            $table->json('payload')->nullable();
            $table->timestamp('scheduled_at')->index();
            $table->timestamp('processing_at')->nullable()->index();
            $table->uuid('processing_token')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->string('provider_message_id')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_deliveries');
        Schema::dropIfExists('referrals');
    }
};
