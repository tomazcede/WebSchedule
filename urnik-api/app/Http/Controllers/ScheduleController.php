<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
    public function show(Request $request) {
        try {
            $schedule = $request->id && $request->id != null ? Schedule::findOrFail($request->id) : Schedule::convertFromJson($request->json);

            return response()->json($schedule->convertToJson($request->from, $request->to));
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Schedule not found'], 404);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        try {
            $validate = $request->validate([
                'name' => 'required|string',
                'user_id' => 'required|integer|exists:users,id',
            ]);

//            if(auth()->user()->id !== $validate['user_id'])
//                return response("Action prohibited", 403);

            $schedule = Schedule::create($validate);

            if($request->events){
                $schedule->addEvents($request->events);
            }

            return response()->json($schedule->convertToJson());
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Schedule not found'], 404);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request) {
        try {
            $request->validate([
                'id' => 'required_without:json|nullable|integer',
                'json' => 'required_without:id|nullable',
            ]);

            $validate = $request->validate([
                'name' => 'required|string',
                'primary_color' => 'sometimes|string|nullable|color',
                'secondary_color' => 'sometimes|string|nullable|color',
                'background_color' => 'sometimes|string|nullable|color',
            ]);

            if($request->json) {
                $schedule = Schedule::convertFromJson($request->json);

                $schedule->name = $request->name;
                $schedule->primary_color = $request->primary_color;
                $schedule->secondary_color = $request->secondary_color;
                $schedule->background_color = $request->background_color;

                return response()->json($schedule->convertToJson());
            } else if($request->id) {
                $schedule = Schedule::find($request->id);
//
//                if(auth()->user()->id !== $schedule->user_id)
//                    return response("Action prohibited", 403);

                $schedule->update($validate);

                return response()->json($schedule->convertToJson());
            }

            return response('Not found', 404);
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Schedule not found'], 404);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addEvents(Request $request) {
        try {
            $request->validate([
                'id' => 'required_without:json|nullable|integer',
                'json' => 'required_without:id|nullable',
            ]);

            $schedule = $request->json ? Schedule::convertFromJson($request->json) : Schedule::find($request->id);
            $schedule->addEvents($request->events);

            return response()->json($schedule->convertToJson());
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Schedule not found'], 404);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeEvent(Request $request) {
        try {
            $request->validate([
                'id' => 'required_without:json|nullable|integer',
                'json' => 'required_without:id|nullable',
            ]);

            if($request->json) {
                $schedule = Schedule::convertFromJson($request->json);
                $schedule->removeEventFromJson($request->event_id);
            } else {
                $schedule = Schedule::find($request->id);
                $schedule->removeEvent($request->event_id);
            }

            return response()->json($schedule->convertToJson());
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Schedule not found'], 404);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request) {
        try {
            $request->validate([
                'id' => 'required_without:json|nullable|integer',
                'json' => 'required_without:id|nullable',
            ]);

            $schedule = $request->json ? Schedule::convertFromJson($request->json) : Schedule::find($request->id);
            $data = Schedule::generateIcal($schedule);

            $filename = 'schedule_' . Str::random(10) . '.ics';
            $path = storage_path('app/' . $filename);

            file_put_contents($path, $data);

            return response()->download($path, $filename, [
                'Content-Type' => 'text/calendar',
            ])->deleteFileAfterSend();
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Schedule not found'], 404);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
