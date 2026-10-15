<?php

namespace App\Console\Commands;

use App\Http\Middleware\DataApiAccess;
use App\Models\Admin;
use Illuminate\Console\Command;

class DataApiToken extends Command
{
    protected $signature = 'data-api:token {admin : Owner admin ID or email}
        {--name= : Project name for the new token}
        {--abilities= : Comma-separated customers:read, orders:read, support:read}
        {--days=7 : Expiration in days, from 1 to 30}
        {--revoke= : Revoke this data API token ID instead of issuing a token}';

    protected $description = 'Issue or revoke an owner-scoped, read-only integration token';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('admin'));
        $admin = ctype_digit($identifier)
            ? Admin::find($identifier)
            : Admin::where('email', $identifier)->first();
        if (!$admin) {
            $this->error('Admin not found.');
            return self::FAILURE;
        }

        if ($this->option('revoke') !== null) {
            $id = (string) $this->option('revoke');
            $token = ctype_digit($id) ? $admin->tokens()->find($id) : null;
            if (!$token || !in_array('data-api', $token->abilities ?? [], true)) {
                $this->error('Data API token not found for this admin.');
                return self::FAILURE;
            }
            $token->delete();
            $this->info('Data API token revoked.');
            return self::SUCCESS;
        }

        $name = trim((string) $this->option('name'));
        $days = (string) $this->option('days');
        $abilities = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $this->option('abilities'))))));
        if (!$admin->isOwner() || $admin->must_change_password) {
            $this->error('Use an owner admin who has completed the required password change.');
            return self::FAILURE;
        }
        if ($name === '' || mb_strlen($name) > 100 || !ctype_digit($days) || (int) $days < 1 || (int) $days > 30
            || !$abilities || array_diff($abilities, DataApiAccess::ABILITIES)) {
            $this->error('Provide a project --name, explicit read --abilities, and --days between 1 and 30.');
            return self::FAILURE;
        }

        $expires = now()->addDays((int) $days);
        $token = $admin->createToken($name, [...$abilities, 'data-api', 'auth-version:'.(int) $admin->auth_version], $expires);
        $this->info('Token ID: '.$token->accessToken->id.'; expires: '.$expires->toIso8601String());
        $this->warn('Keep this token server-side. It is shown once; do not commit or share it.');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
