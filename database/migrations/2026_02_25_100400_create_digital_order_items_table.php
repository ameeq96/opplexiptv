<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_order_id')->constrained('digital_orders')->cascadeOnDelete();
            $table->foreignId('digital_product_id')->nullable()->constrained('digital_products')->nullOnDelete();
            $table->string('product_title');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2);
            $table->enum('delivery_status', ['pending', 'assigned', 'delivered'])->default('pending')->index();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('delivery_payload_id')->nullable()->constrained('digital_delivery_payloads')->nullOnDelete();
            $table->foreignId('assigned_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->json('delivery_meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_order_items');
    }
};
