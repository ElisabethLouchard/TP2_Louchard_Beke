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
            'login' => 'required',
            'password' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'last_name' => 'required',
            'first_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], BAD_REQUEST);
        }

        $user = new User();
        $user->login = $request->input('login');
        $user->password = bcrypt($request->input('password'));
        $user->email = $request->input('email');
        $user->last_name = $request->input('last_name');
        $user->first_name = $request->input('first_name');
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
            $token = $user->createToken('Connexion Token')->plainTextToken;
            return response()->json(['token' => $token], CREATED); 
        } else {
            return response()->json(['error' => 'Erreur d\'authentification'], UNAUTHORIZED); 
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        } else {
            return response()->json(['error' => 'Non authentifié'], UNAUTHORIZED);
        }
    
        return response()->json(null, NO_CONTENT);
    }
}