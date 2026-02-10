<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('footer_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('footer_setting_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('brand_text')->nullable();
            $table->string('crypto_note')->nullable();
            $table->string('address')->nullable();
            $table->string('rights_text')->nullable();
            $table->text('legal_note')->nullable();
            $table->timestamps();

            $table->unique(['footer_setting_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_setting_translations');
    }
};
