<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'password' => 'required',
            'email' => 'required|email:rfc,dns',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], BAD_REQUEST);
        }

        $user = new User();
        $user->id = $request->input('id');
        $user->name = $request->input('name');
        $user->password = bcrypt($request->input('password'));
        $user->email = $request->input('email');
        $user->save();

        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            $token = $user->createToken('User jeton');
            return response()->json(['token' => $token->plainTextToken],OK);
        } else {
            return response()->json(['error' => 'Erreur lors de l\'authentification'], UNAUTHORIZED);
        }
    }
	
	//Copiez votre register complet ici + la route pour y accéder

    public function register(Request $request)
    {
        $userCredentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (auth()->attempt($userCredentials)) {
            return ;
        } else {
            return ;
        }
    }
}
