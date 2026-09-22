<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_normalized')->nullable()->index()->after('email');
            $table->string('phone_normalized', 20)->nullable()->index()->after('phone');
        });

        Schema::table('trial_clicks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('checkout_drafts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('email_normalized')->nullable()->index()->after('email');
            $table->string('phone_normalized', 20)->nullable()->index()->after('phone');
        });

        $normalizeEmail = static function ($value): ?string {
            $email = mb_strtolower(trim((string) $value));

            return $email !== '' ? $email : null;
        };

        $normalizePhone = static function ($value): ?string {
            $phone = preg_replace('/\D+/', '', (string) $value) ?? '';

            return preg_match('/^[1-9]\d{7,14}$/', $phone) === 1 ? $phone : null;
        };

        DB::table('users')
            ->select(['id', 'email', 'phone'])
            ->orderBy('id')
            ->chunkById(400, function ($users) use ($normalizeEmail, $normalizePhone) {
                foreach ($users as $user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'email_normalized' => $normalizeEmail($user->email),
                        'phone_normalized' => $normalizePhone($user->phone),
                    ]);
                }
            });

        $rememberUnique = static function (array &$map, ?string $key, int $id): void {
            if ($key === null) {
                return;
            }

            $map[$key] = array_key_exists($key, $map) ? false : $id;
        };

        $uniqueUserMaps = static function (array $emails, array $phones) use ($rememberUnique): array {
            $emails = array_values(array_unique(array_filter($emails)));
            $phones = array_values(array_unique(array_filter($phones)));

            if ($emails === [] && $phones === []) {
                return [[], []];
            }

            $users = DB::table('users')
                ->select(['id', 'email_normalized', 'phone_normalized'])
                ->where(function ($query) use ($emails, $phones) {
                    if ($emails !== []) {
                        $query->whereIn('email_normalized', $emails);
                    }
                    if ($phones !== []) {
                        $emails === []
                            ? $query->whereIn('phone_normalized', $phones)
                            : $query->orWhereIn('phone_normalized', $phones);
                    }
                })
                ->get();

            $emailUsers = [];
            $phoneUsers = [];
            foreach ($users as $user) {
                $rememberUnique($emailUsers, $user->email_normalized, (int) $user->id);
                $rememberUnique($phoneUsers, $user->phone_normalized, (int) $user->id);
            }

            return [$emailUsers, $phoneUsers];
        };

        $resolveUserId = static function (
            ?string $email,
            ?string $phone,
            array $emailUsers,
            array $phoneUsers
        ): ?int {
            $emailId = $email !== null && array_key_exists($email, $emailUsers) ? $emailUsers[$email] : null;
            $phoneId = $phone !== null && array_key_exists($phone, $phoneUsers) ? $phoneUsers[$phone] : null;

            if ($email !== null) {
                if (! is_int($emailId)) {
                    return null;
                }

                return is_int($phoneId) && $phoneId !== $emailId ? null : $emailId;
            }

            return is_int($phoneId) ? $phoneId : null;
        };

        DB::table('checkout_drafts')
            ->select(['id', 'email', 'phone', 'completed_order_id'])
            ->orderBy('id')
            ->chunkById(400, function ($drafts) use (
                $normalizeEmail,
                $normalizePhone,
                $resolveUserId,
                $uniqueUserMaps
            ) {
                $completedOrderIds = $drafts->pluck('completed_order_id')->filter()->unique();
                $orderUsers = $completedOrderIds->isEmpty()
                    ? collect()
                    : DB::table('orders')->whereIn('id', $completedOrderIds)->pluck('user_id', 'id');
                $normalized = [];

                foreach ($drafts as $draft) {
                    $normalized[$draft->id] = [
                        'email' => $normalizeEmail($draft->email),
                        'phone' => $normalizePhone($draft->phone),
                    ];
                }

                [$emailUsers, $phoneUsers] = $uniqueUserMaps(
                    array_column($normalized, 'email'),
                    array_column($normalized, 'phone')
                );

                foreach ($drafts as $draft) {
                    $email = $normalized[$draft->id]['email'];
                    $phone = $normalized[$draft->id]['phone'];
                    $userId = $draft->completed_order_id
                        ? ($orderUsers->get($draft->completed_order_id) ?: null)
                        : $resolveUserId($email, $phone, $emailUsers, $phoneUsers);

                    DB::table('checkout_drafts')->where('id', $draft->id)->update([
                        'user_id' => $userId,
                        'email_normalized' => $email,
                        'phone_normalized' => $phone,
                    ]);
                }
            });

        DB::table('trial_clicks')
            ->whereNull('user_id')
            ->whereNotNull('phone_normalized')
            ->select(['id', 'phone_normalized'])
            ->orderBy('id')
            ->chunkById(400, function ($clicks) use ($resolveUserId, $uniqueUserMaps) {
                [, $phoneUsers] = $uniqueUserMaps([], $clicks->pluck('phone_normalized')->all());

                foreach ($clicks as $click) {
                    $userId = $resolveUserId(null, $click->phone_normalized, [], $phoneUsers);
                    if ($userId !== null) {
                        DB::table('trial_clicks')->where('id', $click->id)->update(['user_id' => $userId]);
                    }
                }
            });

        DB::table('digital_orders')
            ->whereNull('user_id')
            ->select(['id', 'customer_email', 'customer_phone'])
            ->orderBy('id')
            ->chunkById(400, function ($orders) use (
                $normalizeEmail,
                $normalizePhone,
                $resolveUserId,
                $uniqueUserMaps
            ) {
                $normalized = [];

                foreach ($orders as $order) {
                    $normalized[$order->id] = [
                        'email' => $normalizeEmail($order->customer_email),
                        'phone' => $normalizePhone($order->customer_phone),
                    ];
                }

                [$emailUsers, $phoneUsers] = $uniqueUserMaps(
                    array_column($normalized, 'email'),
                    array_column($normalized, 'phone')
                );

                foreach ($orders as $order) {
                    $userId = $resolveUserId(
                        $normalized[$order->id]['email'],
                        $normalized[$order->id]['phone'],
                        $emailUsers,
                        $phoneUsers
                    );

                    if ($userId !== null) {
                        DB::table('digital_orders')->where('id', $order->id)->update(['user_id' => $userId]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('checkout_drafts', function (Blueprint $table) {
                $table->dropIndex(['email_normalized']);
                $table->dropIndex(['phone_normalized']);
            });
            Schema::table('checkout_drafts', function (Blueprint $table) {
                $table->dropColumn(['user_id', 'email_normalized', 'phone_normalized']);
            });

            Schema::table('trial_clicks', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        } else {
            Schema::table('checkout_drafts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
                $table->dropColumn(['email_normalized', 'phone_normalized']);
            });

            Schema::table('trial_clicks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['email_normalized']);
                $table->dropIndex(['phone_normalized']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_normalized', 'phone_normalized']);
        });
    }
};
