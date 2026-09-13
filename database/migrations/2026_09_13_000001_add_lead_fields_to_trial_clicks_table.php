<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->string('intent', 32)->default('trial')->after('event_id');
            $table->string('placement', 128)->nullable()->after('intent');
            $table->string('package_name', 191)->nullable()->after('placement');
            $table->string('vendor', 32)->nullable()->after('package_name');
            $table->decimal('value', 10, 2)->nullable()->after('vendor');
            $table->char('currency', 3)->nullable()->after('value');
            $table->string('status', 20)->default('new')->index()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'intent',
                'placement',
                'package_name',
                'vendor',
                'value',
                'currency',
                'status',
            ]);
        });
    }
};
