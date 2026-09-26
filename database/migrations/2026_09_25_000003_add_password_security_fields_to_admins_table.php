<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $rotationAdminId = null;
        $rotationPassword = null;

        if (DB::table('admins')->exists()) {
            $rotationEmail = (string) config('auth.admin_seed.email');
            $rotationPassword = (string) config('auth.admin_seed.password');

            if (!$this->isStrongRotationPassword($rotationPassword) || $rotationEmail === '') {
                throw new RuntimeException(
                    'Set ADMIN_SEED_EMAIL to an existing admin and ADMIN_SEED_PASSWORD to a new strong temporary password before running this migration.'
                );
            }

            $rotationAdminId = DB::table('admins')->where('email', $rotationEmail)->value('id');

            if (!$rotationAdminId) {
                throw new RuntimeException('ADMIN_SEED_EMAIL must match an existing admin before running this migration.');
            }
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(true)->after('password');
            $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
            $table->rememberToken()->after('password_changed_at');
            $table->unsignedInteger('auth_version')->default(1)->after('remember_token');
        });

        if ($rotationAdminId !== null) {
            DB::table('admins')->where('id', $rotationAdminId)->update([
                'password' => Hash::make($rotationPassword),
                'must_change_password' => true,
                'password_changed_at' => null,
                'remember_token' => Str::random(60),
                'auth_version' => 2,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'password_changed_at', 'remember_token', 'auth_version']);
        });
    }

    private function isStrongRotationPassword(string $password): bool
    {
        return strlen($password) >= 12
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^A-Za-z0-9]/', $password) === 1;
    }
};
