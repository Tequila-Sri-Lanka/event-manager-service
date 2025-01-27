<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;
    protected $fillable = [
        'guest_id',
        'key',
        'emailed_at',
        'attended_at'
    ];

    protected $hidden = [
        'key'
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function event()
    {
        return $this->guest->event;
    }
}
