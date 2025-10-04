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
        Schema::create('trial_clicks', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->index();
            $table->string('destination', 512)->nullable();
            $table->string('page', 512)->nullable();
            $table->string('fbp', 128)->nullable();
            $table->string('fbc', 256)->nullable();
            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('utm_source', 128)->nullable();
            $table->string('utm_medium', 128)->nullable();
            $table->string('utm_campaign', 128)->nullable();
            $table->string('utm_term', 128)->nullable();
            $table->string('utm_content', 128)->nullable();
            $table->text('referrer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_clicks');
    }
};
