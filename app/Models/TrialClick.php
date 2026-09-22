<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialClick extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'replied', 'qualified', 'paid', 'lost'];
    public const WHATSAPP_CONTACT_CONSENT_VERSION = '2026-09-15.1';

    protected $guarded = [];

    protected $casts = [
        'value' => 'decimal:2',
        'whatsapp_contact_consented_at' => 'datetime',
        'click_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLeadCodeAttribute(): string
    {
        return 'OPX-'.strtoupper(substr(str_replace('-', '', (string) $this->event_id), 0, 8));
    }
}
