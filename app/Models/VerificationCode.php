<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'personal_access_token_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function  token(){
        $this->hasOne(PersonalAccessToken::class);
    }
}
