<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'country',
        'notes',
        'marketing_email_consented_at',
        'marketing_email_opted_out_at',
        'marketing_whatsapp_consented_at',
        'marketing_whatsapp_opted_out_at',
        'marketing_ads_consented_at',
        'marketing_ads_opted_out_at',
        'marketing_consent_version',
        'marketing_consent_source',
        'marketing_consent_locale',
        'marketing_consent_ip_hash',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'marketing_email_consented_at' => 'datetime',
        'marketing_email_opted_out_at' => 'datetime',
        'marketing_whatsapp_consented_at' => 'datetime',
        'marketing_whatsapp_opted_out_at' => 'datetime',
        'marketing_ads_consented_at' => 'datetime',
        'marketing_ads_opted_out_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasMarketingConsent(string $channel): bool
    {
        $consentedAt = $this->getAttribute("marketing_{$channel}_consented_at");
        $optedOutAt = $this->getAttribute("marketing_{$channel}_opted_out_at");

        return $consentedAt !== null && ($optedOutAt === null || $consentedAt->gt($optedOutAt));
    }
}
