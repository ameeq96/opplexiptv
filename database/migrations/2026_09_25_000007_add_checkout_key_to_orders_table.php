<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_key')->nullable()->after('id');
            $table->unique('checkout_key', 'orders_checkout_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_checkout_key_unique');
            $table->dropColumn('checkout_key');
        });
    }
};
