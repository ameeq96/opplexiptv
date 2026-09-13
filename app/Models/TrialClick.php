<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialClick extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'replied', 'qualified', 'paid', 'lost'];

    protected $guarded = [];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function getLeadCodeAttribute(): string
    {
        return 'OPX-'.strtoupper(substr(str_replace('-', '', (string) $this->event_id), 0, 8));
    }
}
