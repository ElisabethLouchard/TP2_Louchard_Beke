<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
	//Copiez votre register complet ici + la route pour y accéder

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], BAD_REQUEST);
        }

        $user = new User();
        $user->name = $request->input('name');
        $user->password = bcrypt($request->input('password'));
        $user->email = $request->input('email');
        $user->save();

        $token = $user->createToken('User Token')->plainTextToken;

        return response()->json(['token' => $token], CREATED); 
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email:rfc,dns',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = $request->user();
            $token = $user->createToken('User Token')->plainTextToken;
            return response()->json(['token' => $token], CREATED); 
        } else {
            return response()->json(['error' => 'Erreur d\'authentification'], UNAUTHORIZED); 
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); 

        return response()->json(null, NO_CONTENT); 


    }
}