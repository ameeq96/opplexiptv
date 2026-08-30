<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->date('review_date')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('device', 100)->nullable();
            $table->string('verification_source', 120)->nullable();
            $table->string('proof_reference', 191)->nullable();
            $table->timestamp('publication_consented_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn([
                'is_verified',
                'verified_at',
                'review_date',
                'country',
                'device',
                'verification_source',
                'proof_reference',
                'publication_consented_at',
            ]);
        });
    }
};
