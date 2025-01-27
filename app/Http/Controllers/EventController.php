<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        $query = Event::query();
        // Admins can see all events
        if(Gate::denies('manage-events')){
            $query->where('user_id', Auth::user()->id);
        } elseif($request->user_id){
            // Filter events by user
            $query->where('user_id', $request->user_id);
        }

        // Filter events by status
        if($status = $request->status){
            if($status == 'completed'){
                $query->where('end', '<', now());
            } elseif($status == 'active'){
                $query->where('start', '<=', now());
                $query->where('end', '>=', now());
            } elseif($status == 'upcoming'){
                $query->where('end', '>', now());
            }
        }

        // Search events across multiple fields
        if($request->search){
            $query->where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('description', 'LIKE', '%' . $request->search . '%')
                ->orWhere('venue', 'LIKE', '%' . $request->search . '%');
        }

        $events = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Events listed',
            'events' => $events,
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        if(Gate::denies('manage-events')){
            abort(401, 'Unauthorized.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'venue' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created',
            'event' => $event,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Event shown',
            'event' => $event,
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        if(Gate::denies('manage-events')){
            abort(401, 'Unauthorized.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'venue' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated',
            'event' => $event,
        ]);
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        if(Gate::denies('manage-events')){
            abort(401, 'Unauthorized.');
        }

        $event->delete();
        return response()->json([
            'success' => true,
            'message' => 'Event deleted'
        ]);
    }
}
