<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvitationController extends Controller
{
    /**
     * Invite the specified guest.
     */
    public function invite(Event $event, Guest $guest, Request $request)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        // Check if the guest belongs to this event
        if($guest->event_id != $event->id){
            abort(404, 'Not Found.');
        }

        $guest->invite($request->reinvite ?? false);

        return response()->json([
            'success' => true,
            'message' => 'Guest invited'
        ]);
    }

    /**
     * Invite all guests.
     */
    public function invite_all(Event $event)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        $event->guests()->each(function ($guest){
            $guest->invite();
        });

        return response()->json([
            'success' => true,
            'message' => 'Guests invited'
        ]);
    }

    /**
     * Invite selected guests.
     */
    public function invite_selected(Event $event, Request $request)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        $request->validate([
            'guest_ids' => 'required'
        ]);
        
        $selected_guests = $event->guests()->wherein('id', $request->guest_ids);

        $selected_guests->each(function ($guest){
            $guest->invite();
        });

        return response()->json([
            'success' => true,
            'message' => 'Selected guests invited'
        ]);
    }

    /**
     * Attend an event
     */
    public function attend(Event $event, Request $request)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        if($event->start->isFuture() || $event->end->isPast()){
            return response()->json([
                'success' => false,
                'message' => 'Event is not currently active'
            ]);
        }

        $request->validate([
            'key' => 'required'
        ]);

        $invitation = Invitation::where('key', $request->key)->first();

        if(!$invitation){
            return response()->json([
                'success' => false,
                'message' => 'Invalid key'
            ]);
        }

        if($invitation->attended_at){
            return response()->json([
                'success' => false,
                'message' => 'Already attended'
            ]);
        }

        if($invitation->event()->id != $event->id){
            return response()->json([
                'success' => false,
                'message' => 'Invalid key'
            ]);
        }

        $invitation->update([
            'attended_at' => now()
        ]);

        Scan::create([
            'user_id' => Auth::user()->id,
            'event_id' => $invitation->event()->id,
            'guest_id' => $invitation->guest->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attended successfuly',
            'guest' => $invitation->guest
        ]);
    }
}
