<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestimonialTranslation extends Model
{
    protected $fillable = ['testimonial_id', 'locale', 'author_name', 'text'];
}
