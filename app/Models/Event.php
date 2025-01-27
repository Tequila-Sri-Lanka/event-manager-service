<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'venue',
        'start',
        'end'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function scans()
    {
        return $this->hasMany(Scan::class)->orderBy('created_at', 'desc');
    }

    public function isActive()
    {
        return $this->start <= now() && $this->end >= now();
    }
}
