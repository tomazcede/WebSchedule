<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Schedule;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function show(Event $event) {
        try {
            return response()->json($event);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paginate(Request $request) {
        try {
            $query = Event::query()->where('is_public', true);

            $filter = $request->filter;

            if($filter['search']){
                $query->whereLike('name', '%'.$filter['search'].'%');
            }

            if($filter['faculty_id']){
                $query->where('faculty_id', $filter['faculty_id']);
            }

            return response()->json($query->paginate($filter['per_page'] ?? 10));
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function parseEvents(Request $request) {
        $file = $request->file('file');

        $events = Event::parseEventsFromFile($file, $request->faculty_id);

        return response()->json(compact('events'));
    }

    public function update(Request $request) {
        try{
            $request->validate([
                'schedule_id' => 'required_without:json|nullable|integer',
                'json' => 'required_without:schedule_id|nullable',
            ]);

            if($request->json) {
                $schedule = Schedule::convertFromJson($request->json);
                $schedule->updateJsonEvent($request->event);
            } else {
                $schedule = Schedule::find($request->schedule_id);
                $event = Event::find($request->event['id']);

                if(auth()->user() == null || auth()->user()->id != $event->user_id) {
                    return response()->json(['error' => 'Unauthorized.'], 403);
                }

                $event->update($request->event);
            }

            return response()->json($schedule->convertToJson());
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
