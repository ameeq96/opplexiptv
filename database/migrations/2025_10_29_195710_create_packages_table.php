<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
          $table->id();

            // Common
            $table->string('type')->index();                 // iptv | reseller | connection
            $table->string('vendor')->nullable();
            $table->string('title');                         // e.g. "Monthly", "Pro Reseller Suite"

            // Price (dual: display + numeric)
            $table->string('display_price')->nullable();     // e.g. "$2.99 / monthly" (localized or with HTML)
            $table->decimal('price_amount', 10, 2)->nullable(); // 2.99 (optional for math)

            // Visuals & meta
            $table->string('icon')->nullable();              // single icon CSS class or path (optional)
            $table->json('icons')->nullable();               // reseller needs multiple icons
            $table->json('features')->nullable();            // array of strings (translated keys already resolved)

            // Extra fields (optional, useful for sorting / other types)
            $table->unsignedInteger('duration_months')->nullable(); // IPTV duration
            $table->unsignedInteger('credits')->nullable();         // Reseller credits
            $table->unsignedInteger('max_devices')->nullable();     // Connection plans
            $table->unsignedInteger('sort_order')->nullable();
            $table->integer('plan_id')->nullable();

            // Buttons / UX sugar
            $table->string('button_link')->nullable();       // e.g. 'buy-now-panel'
            $table->string('delay')->nullable();             // e.g. '150ms' (for animations)

            $table->boolean('active')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
