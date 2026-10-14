<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            foreach (['users', 'admins', 'orders'] as $name) {
                $table = DB::selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$name]);
                if (strtolower($table->engine ?? '') !== 'innodb') {
                    throw new RuntimeException('Customer CRM requires InnoDB for '.$name.'. Convert it only after an approved backup.');
                }
            }
        }
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
        });

        Schema::create('customer_activities', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('source', 32);
            $table->string('event_id', 191);
            $table->string('event_type', 80);
            $table->string('entity_type', 40)->nullable();
            $table->string('entity_id', 191)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('contact_name', 255)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->text('message')->nullable();
            $table->json('details')->nullable();
            $table->char('payload_hash', 64);
            $table->unsignedBigInteger('source_sequence')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique(['source', 'event_id']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['source', 'entity_type', 'entity_id'], 'customer_activity_entity_idx');
        });

        Schema::create('customer_conversations', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('channel', 32);
            $table->string('property_id', 100);
            $table->string('external_id', 191);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_name', 255)->nullable();
            $table->string('visitor_email', 255)->nullable();
            $table->string('visitor_phone', 50)->nullable();
            $table->string('status', 32)->default('open');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['channel', 'property_id', 'external_id'], 'customer_conversation_external_unique');
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('customer_messages', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('conversation_id')->constrained('customer_conversations')->cascadeOnDelete();
            $table->char('fingerprint', 64);
            $table->string('sender_type', 32);
            $table->string('sender_name', 191)->nullable();
            $table->text('content');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['conversation_id', 'fingerprint'], 'customer_message_fingerprint_unique');
            $table->index(['conversation_id', 'sent_at']);
        });

        Schema::create('customer_follow_ups', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('note');
            $table->timestamp('due_at');
            $table->string('status', 24)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'due_at']);
            $table->index(['assigned_admin_id', 'status', 'due_at'], 'customer_follow_up_assignee_idx');
        });

        Schema::create('customer_external_links', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('source', 32);
            $table->string('entity_type', 40);
            $table->string('external_id', 191);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->json('state')->nullable();
            $table->timestamps();
            $table->unique(['source', 'entity_type', 'external_id'], 'customer_external_link_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_external_links');
        Schema::dropIfExists('customer_follow_ups');
        Schema::dropIfExists('customer_messages');
        Schema::dropIfExists('customer_conversations');
        Schema::dropIfExists('customer_activities');
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('assigned_admin_id'));
    }
};
