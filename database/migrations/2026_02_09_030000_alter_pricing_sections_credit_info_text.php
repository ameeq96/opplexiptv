<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_sections', function (Blueprint $table) {
            $table->text('credit_info')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pricing_sections', function (Blueprint $table) {
            $table->text('credit_info')->nullable()->change();
        });
    }
};
