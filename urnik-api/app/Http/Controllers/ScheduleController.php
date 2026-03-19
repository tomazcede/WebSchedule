<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
    public function show(Request $request) {
        try {
            $request->validate([
                'id' => 'required_without:json|nullable|integer',
                'json' => 'required_without:id|nullable',
            ]);

            $scheduleService = new ScheduleService($request->id ?? $request->json);

            return response()->json($scheduleService->convertToJson($request->from, $request->to));
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

            $scheduleService = new ScheduleService(Schedule::create($validate));

            if($request->events){
                $scheduleService->addEvents($request->events);
            }

            return response()->json($scheduleService->convertToJson());
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

            $scheduleService = new ScheduleService($request->id ?? $request->json);
            $scheduleService->updateSchedule($validate);

            return response()->json($scheduleService->convertToJson());
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
                'events' => 'required|array',
            ]);

            $scheduleService = new ScheduleService($request->id ?? $request->json);
            $scheduleService->addEvents($request->events);

            return response()->json($scheduleService->convertToJson());
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
                'event_id' => 'required'
            ]);

            $scheduleService = new ScheduleService($request->id ?? $request->json);
            $scheduleService->removeEvent($request->event_id);

            return response()->json($scheduleService->convertToJson());
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

            $data = (new ScheduleService($request->id ?? $request->json))->generateIcal();
            $path = storage_path('app/schedule_' . Str::random(10) . '.ics');

            file_put_contents($path, $data);

            return response()->download($path, 'schedule.ics', [
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
