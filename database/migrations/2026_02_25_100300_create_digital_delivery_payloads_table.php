<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_delivery_payloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_product_id')->constrained('digital_products')->cascadeOnDelete();
            $table->enum('payload_type', ['credential', 'code', 'link', 'file', 'manual']);
            $table->text('payload');
            $table->boolean('is_assigned')->default(false)->index();
            $table->unsignedBigInteger('assigned_order_item_id')->nullable()->unique();
            $table->foreignId('assigned_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['digital_product_id', 'is_assigned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_delivery_payloads');
    }
};
