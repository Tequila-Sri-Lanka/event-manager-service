<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GuestController extends Controller
{
    /**
     * Display a listing of the guests.
     */
    public function index(Event $event)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Guests listed',
            'counts' => [
                "total" => $event->guests->count(),
                "emailed" => $event->guests->where(function ($guest) {
                    return $guest?->invitation?->emailed_at != null;
                })->count(),
                "attended" => $event->guests->where(function ($guest) {
                    return $guest?->invitation?->attended_at != null;
                })->count()
            ],
            'guests' => $event->guests->load('invitation')
        ]);
    }

    /**
     * Store a newly created guest in storage.
     */
    public function store(Request $request, Event $event)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'nullable',
            'email' => 'required',
            'company' => 'nullable',
            'table' => 'nullable',
            'seat' => 'nullable',
        ]);

        $guest = $event->guests()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guest created',
            'guest' => $guest->load('invitation')
        ]);
    }

    /**
     * Display the specified guest.
     */
    public function show(Event $event, Guest $guest)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        // Check if the guest belongs to this event
        if($guest->event_id != $event->id){
            abort(404, "Not Found");
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest shown',
            'guest' => $guest->load('invitation')
        ]);
    }

    /**
     * Update the specified guest in storage.
     */
    public function update(Request $request, Event $event, Guest $guest)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        // Check if the guest belongs to this event
        if($guest->event_id != $event->id){
            abort(404, "Not Found");
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'nullable',
            'email' => 'required',
            'company' => 'nullable',
            'table' => 'nullable',
            'seat' => 'nullable',
        ]);

        $guest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guest updated',
            'guest' => $guest->load('invitation')
        ]);
    }

    /**
     * Remove the specified guest from storage.
     */
    public function destroy(Event $event, Guest $guest)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        // Check if the guest belongs to this event
        if($guest->event_id != $event->id){
            abort(404, "Not Found");
        }

        $guest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guest deleted'
        ]);
    }

    /**
     * Import guests from a CSV
     */
    public function upload(Request $request, Event $event)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        $request->validate([
            'guests' => 'required|file'
        ]);

        // Parse the CSV
        $data = array_map('str_getcsv', file($request->guests->getRealPath()));
        $headings = array_shift($data);
        foreach($data as $row){
            $guests[] = array_combine($headings, $row);
        }

        // Create guests
        $event->guests()->createMany($guests);

        return response()->json([
            'success' => true,
            'message' => 'Guests imported',
            'guests' => $event->guests
        ]);
    }

    /**
     * Output the list of guests as CSV
     */
    public function download(Event $event)
    {
        if(Gate::denies('access-event', $event)){
            abort(404, 'Not Found.');
        }

        $file = fopen('php://output', 'w');
        fputcsv($file, ['first_name', 'last_name', 'email', 'company', 'invited', 'attended']);

        foreach ($event->guests as $guest) {
            fputcsv($file, [
                $guest->first_name,
                $guest->last_name,
                $guest->email,
                $guest->company,
                $guest->invitation?->emailed_at ? 'yes' : 'no',
                $guest->invitation?->attended_at ? 'yes' : 'no',
            ]);
        }

        fclose($file);
    }
}
