<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('brand', 20);
            $table->string('locale', 12);
            $table->string('display_name', 80);
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->string('status', 20)->default('pending');
            $table->timestamp('publication_consented_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->unsignedBigInteger('moderated_by_admin_id')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['brand', 'locale', 'status']);
        });

        Schema::create('service_incidents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('brand', 20);
            $table->string('locale', 12);
            $table->string('title', 160);
            $table->text('body');
            $table->string('state', 20);
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('updated_by_admin_id')->nullable();
            $table->timestamps();
            $table->index(['brand', 'locale', 'is_published']);
        });

        Schema::create('setup_media', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('brand', 20);
            $table->string('locale', 12);
            $table->string('guide_slug', 160);
            $table->string('title', 160);
            $table->text('description');
            $table->string('kind', 12);
            $table->string('media_url', 500);
            $table->string('alt_text', 240);
            $table->string('device_model', 160);
            $table->string('player_name', 120);
            $table->string('player_version', 80);
            $table->timestamp('tested_at');
            $table->string('evidence_reference', 191);
            $table->timestamp('publication_permission_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('updated_by_admin_id')->nullable();
            $table->timestamps();
            $table->index(['brand', 'locale', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_media');
        Schema::dropIfExists('service_incidents');
        Schema::dropIfExists('customer_reviews');
    }
};
