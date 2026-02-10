<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('home_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_service_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['home_service_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_service_translations');
    }
};
