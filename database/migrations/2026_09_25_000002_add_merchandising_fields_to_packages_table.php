<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('badge_key')->nullable()->after('delay');
            $table->boolean('is_featured')->default(false)->index()->after('badge_key');
            $table->boolean('is_available')->default(true)->index()->after('is_featured');
            $table->unsignedSmallInteger('free_trial_hours')->nullable()->after('is_available');
            $table->boolean('instant_activation')->default(false)->after('free_trial_hours');
            $table->json('connection_prices')->nullable()->after('instant_activation');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_available']);
            $table->dropColumn([
                'badge_key',
                'is_featured',
                'is_available',
                'free_trial_hours',
                'instant_activation',
                'connection_prices',
            ]);
        });
    }
};
