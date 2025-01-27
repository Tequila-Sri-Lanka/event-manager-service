<?php

namespace App\Models;

use App\Mail\InvitationEmail;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'email',
        'company',
        'table',
        'seat'
    ];

    protected $appends = [
        'emailed_at',
        'attended_at'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function GetEmailedAtAttribute()
    {
        return $this->invitation->emailed_at ?? null;
    }

    public function GetAttendedAtAttribute()
    {
        return $this->invitation->attended_at ?? null;
    }

    public function invitation()
    {
        return $this->hasOne(Invitation::class);
    }

    public function invite($reinvite = false)
    {
        $invitation = $this->invitation()->firstOrCreate([],[
            'key' => uuid_create()
        ]);

        if(!$invitation->emailed_at || $reinvite){
            try{
                Mail::to($this)->send(new InvitationEmail($invitation));
            } catch(Exception $e){
                return false;
            }
            $invitation->emailed_at = now();
            $invitation->save();
        }
    }
}
