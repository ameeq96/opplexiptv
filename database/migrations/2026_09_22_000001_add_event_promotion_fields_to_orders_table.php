<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->nullable()->after('sell_price');
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->string('promotion_campaign_id', 100)->nullable()->after('discount');
            $table->unsignedTinyInteger('promotion_discount_percent')->nullable()->after('promotion_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount',
                'promotion_campaign_id',
                'promotion_discount_percent',
            ]);
        });
    }
};
