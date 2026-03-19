<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(User $user){
        return response()->json($user);
    }

    public function delete(Request $request, User $user){
        if($request->user()->id !== $user->id)
            return response("Action prohibited", 403);

        $user->delete();
        return response("User deleted", 200);
    }

    public function store(Request $request, User $user){
        if($request->user()->id !== $user->id)
            return response("Action prohibited", 403);

        $user->fill($request->all());
        $user->save();
        return response()->json($user);
    }

    public function getCurrent(Request $request)
    {
        if($user = $request->user()) {
            Auth::loginUsingId($user->id);
            return response()->json(['user' => $user]);
        }

        return response()->json(['error' => 'Unauthorized'], 200);
    }
}
