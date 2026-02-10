<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('social_link_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_link_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('platform')->nullable();
            $table->timestamps();

            $table->unique(['social_link_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_link_translations');
    }
};
