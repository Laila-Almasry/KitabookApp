<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\VisitReservation;
use App\Http\Requests\VisitReservationRequest;
use Illuminate\Support\Facades\Auth;

class VisitReservationController extends Controller
{
    //helper function to get the id of the user
    public function getAuthenticatedUserId() {
    $user = Auth::user();
    return $user instanceof \App\Models\User ? $user->id : null;
}

public function show($id){
    $visit=VisitReservation::findOrFail($id);
    return response()->json(['visit'=>$visit],200);
}
    // GET available times
    public function checkAvailableTimes(Request $request)
    {
        $request->validate([
            'visit_date' => 'required|date|after_or_equal:today',
            'duration' => 'required|in:1,2,3',
        ]);

        $date = $request->visit_date;
        $duration = (int) $request->duration;

        $startHour = 8;
        $endHour = 20 - $duration;

        $available = [];

        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $start = Carbon::createFromTime($hour, 0, 0);
            $end = (clone $start)->addHours($duration);

            $count = VisitReservation::where('visit_date', $date)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_time', [$start->format('H:i'), $end->format('H:i')])
                          ->orWhereBetween('end_time', [$start->format('H:i'), $end->format('H:i')]);
                })
                ->count();

            if ($count < 20) {
                $available[] = $start->format('H:i');
            }
        }

        return response()->json(['available_times' => $available]);
    }

    // POST /bookReservation
    public function store(VisitReservationRequest $request)
    {
        $start = Carbon::createFromFormat('H:i', $request->start_time);
        $end = (clone $start)->addHours((int)$request->duration);

        $conflicts = VisitReservation::where('visit_date', $request->visit_date)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_time', [$start->format('H:i'), $end->format('H:i')])
                      ->orWhereBetween('end_time', [$start->format('H:i'), $end->format('H:i')]);
            })->count();

        if ($conflicts >= 20) {
            return response()->json(['message' => 'This time slot is full.'], 422);
        }

        $reservation = VisitReservation::create([
            'user_id' => VisitReservationController::getAuthenticatedUserId(),
            'visit_date' => $request->visit_date,
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'guest_name'=>$request->guest_name,
            'status'=>$request->status,
        ]);

        return response()->json(['message' => 'Reservation successful.', 'reservation' => $reservation]);
    }

    // GET /myReservations
    public function myReservations()
    {
        $user = Auth::user();

        // Optionally, auto-mark expired as "done" in a scheduler/command
        $reservations = VisitReservation::where('user_id', $user->id)
            ->orderByDesc('visit_date')
            ->get();

        return response()->json($reservations);
    }

    // DELETE /bookReservation/{id}
    public function cancel($id)
    {
        $reservation = VisitReservation::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Only pending reservations can be cancelled.'], 403);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['message' => 'Reservation cancelled.']);
    }

    public function index()
{
    // Eager load the user relationship
    $visits = VisitReservation::with('user')->get();

    // Map each visit with user_name included
    $data = $visits->map(function ($visit) {
        return [
            'id' => $visit->id,
            'guest_name' => $visit->guest_name,
            'visit_date' => $visit->visit_date,
            'start_time' => $visit->start_time,
            'end_time' => $visit->end_time,
            'status' => $visit->status,
            'code' => $visit->guest_name?$visit->code:'',
            'username' => $visit->user->username ?? 'Guest',
        ];
    });

    return response()->json([
        'visits' => $data,
    ], 200);
}


    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,checked_in,done,cancelled',
    ]);

    $visit = VisitReservation::findOrFail($id);

    // Optional: prevent owner from marking already cancelled/done reservations
    if (in_array($visit->status, ['cancelled', 'done'])) {
        return response()->json(['message' => 'Cannot update a cancelled or completed reservation.'], 403);
    }

    $visit->status = $request->status;
    $visit->save();

    return response()->json([
        'message' => 'Visit status updated successfully.',
        'visit' => $visit
    ]);
}

public function check(Request $request)
{
    $request->validate([
        'visit_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    $visitDate = $request->visit_date;
    $startTime = $request->start_time;
    $endTime = $request->end_time;

    // Count overlapping visits
    $overlappingVisits = VisitReservation::where('visit_date', $visitDate)
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });
        })
        ->count();

    $maxVisitors = 20;

    return response()->json([
        'available' => $overlappingVisits < $maxVisitors,
        'current' => $overlappingVisits,
        'remaining' => $maxVisitors - $overlappingVisits
    ]);
}


}
