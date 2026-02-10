<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pricing_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('heading')->nullable();
            $table->string('subheading')->nullable();
            $table->string('show_reseller_label')->nullable();
            $table->text('credit_info')->nullable();
            $table->timestamps();

            $table->unique(['pricing_section_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_section_translations');
    }
};
