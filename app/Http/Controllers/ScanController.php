<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScanController extends Controller
{
    public function index(Event $event)
    {
        if (Gate::denies('access-event', $event)) {
            abort(404, 'Not Found.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Scans listed',
            'scans' => $event->scans->load('guest')
        ]);
    }

    /**
     * Stream new scans
     *
     * @return \Illuminate\Http\Response
     */
    public function stream(Event $event, Request $request)
    {
        if (Gate::denies('access-event', $event)) {
            abort(404, 'Not Found.');
        }

        if(!$event->isActive()){
            abort('403', 'Event is not active');
        }

        set_time_limit(0);
        $eventId = $event->id;
        $token = $request->user()->currentAccessToken();

        return response()->stream(function () use ($eventId, $token) {

            // set this to last scan's id to only show scans
            // created after connection
            $lastScanId = -1;

            while (true) {
                // $event->scans is cached(?) so find the last scan this way
                $scan = Scan::where('event_id', $eventId)
                    ->orderBy('id', 'desc')
                    ->first();

                // using > instead of != prevents it executing
                // when a scan is deleted
                if ($scan && $scan?->id > $lastScanId) {
                    $lastScanId = $scan->id;

                    echo "data: " . json_encode([
                        "scan" => $scan->load('guest'),
                        'time' => now()
                    ]) . "\n\n";

                    ob_flush();
                    flush();

                    // update token expiry
                    $token->update([
                        'expires_at' => now()->addHours(env('SESSION_TIMEOUT_HRS', 2))
                    ]);
                }

                // break the loop if the client aborted the connection (closed the page)
                if (connection_aborted()) {
                    break;
                }

                sleep(1); // wait a second
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive'
        ]);
    }
}
