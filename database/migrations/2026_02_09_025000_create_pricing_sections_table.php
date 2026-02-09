<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_sections', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->string('subheading')->nullable();
            $table->string('show_reseller_label')->nullable();
            $table->string('credit_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_sections');
    }
};
