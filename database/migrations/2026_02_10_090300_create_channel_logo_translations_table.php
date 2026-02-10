<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('channel_logo_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_logo_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('alt_text')->nullable();
            $table->timestamps();

            $table->unique(['channel_logo_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_logo_translations');
    }
};
