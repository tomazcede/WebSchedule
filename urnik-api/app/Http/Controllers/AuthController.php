<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($data)) {
            return response()->json(['message' => 'Invalid login'], 401);
        }

        $request->session()->regenerate();

        return response()->json(['user' => Auth::user()]);
    }
    public function logout(){
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request){
        try{
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string',
                'confirm_password' => 'required|string|same:password',
                'faculty_id' => 'required|exists:faculties,id'
            ]);

            unset($data['confirm_password']);
            $data['password'] = bcrypt($data['password']);

            $user = User::create($data);
            $user->schedules()->create(['name' => 'new']);
            $user->refresh();

            Auth::login($user);

            $request->session()->regenerate();

            return response(['user' => $user]);
        } catch(\Exception $e){
            return response(['message' => $e->getMessage()], 500);
        }
    }
}
