<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository) 
    {
        $this->userRepository = $userRepository;
    }

    public function update(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié.'], NOT_FOUND);
            }
    
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json(['error' => 'Mot de passe actuel incorrect.'], FORBIDDEN);
            }
    
            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6',
                'new_password_confirmation' => 'required|same:new_password', 
            ]);

            $user->password = Hash::make($validatedData['new_password']);
            $user->save();
    
            return response()->json(['message' => 'Mot de passe mis à jour avec succès.'], OK);
    
        } catch (\Exception $ex) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du mot de passe.'], SERVER_ERROR);
        }
    }

}
