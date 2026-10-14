<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFollowUp extends Model
{
    protected $guarded = [];

    protected $casts = ['due_at' => 'datetime', 'completed_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function assignedAdmin() { return $this->belongsTo(Admin::class, 'assigned_admin_id'); }
    public function createdByAdmin() { return $this->belongsTo(Admin::class, 'created_by_admin_id'); }
}
