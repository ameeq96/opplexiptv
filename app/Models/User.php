<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Digital\DigitalOrder;
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
        'email_normalized',
        'phone_normalized',
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

    public function trialClicks()
    {
        return $this->hasMany(TrialClick::class);
    }

    public function checkoutDrafts()
    {
        return $this->hasMany(CheckoutDraft::class);
    }

    public function digitalOrders()
    {
        return $this->hasMany(DigitalOrder::class);
    }

    public function marketingDeliveries()
    {
        return $this->hasMany(MarketingDelivery::class);
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_user_id');
    }

    public function referralsReceived()
    {
        return $this->hasMany(Referral::class, 'referred_user_id');
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value;
        $this->attributes['email_normalized'] = self::normalizeEmail($value);
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value;
        $this->attributes['phone_normalized'] = self::normalizePhone($value);
    }

    public static function normalizeEmail($value): ?string
    {
        $email = mb_strtolower(trim((string) $value));

        return $email !== '' ? $email : null;
    }

    public static function normalizePhone($value): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $value) ?? '';

        return preg_match('/^[1-9]\d{7,14}$/', $phone) === 1 ? $phone : null;
    }

    public function hasMarketingConsent(string $channel): bool
    {
        $consentedAt = $this->getAttribute("marketing_{$channel}_consented_at");
        $optedOutAt = $this->getAttribute("marketing_{$channel}_opted_out_at");

        return $consentedAt !== null && ($optedOutAt === null || $consentedAt->gt($optedOutAt));
    }
}
