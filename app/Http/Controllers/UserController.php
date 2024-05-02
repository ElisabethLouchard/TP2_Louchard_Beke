<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository) 
    {
        $this->userRepository = $userRepository;
    }

    public function update(Request $request, $id)
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], NOT_FOUND);
        }
    
        if (Auth::user()->id !== $id) {
            return response()->json(['error' => 'Vous n\'avez pas les autorisations nécessaires pour cette action.'], FORBIDDEN);
        }
    
        $validatedData = $request->validate([
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ]);
    
        $user->password = bcrypt($validatedData['new_password']);
        $user->save();
    
        return response()->json(['message' => 'Mot de passe mis à jour avec succès.'], OK);
    }

    public function show(int $user_id)
    {
        if(Auth::check())
        {
            $user = Auth::user();
            if($user->id == $user_id)
            {
                return response()->json(
                    ['login' => $user->login, 
                    'email' => $user->email, 
                    'last_name' => $user->last_name, 
                    'first_name' => $user->first_name
                ], OK
                );
            }
            else
            {
                return response()->json(['message' => "L'utilsateur n'a pas les permissions pour afficher cet utilisateur"], FORBIDDEN); 
            }
    
        }
        else if(Auth::check() == false)
        {
            return response()->json(['message' => "L'utilsateur n'est pas authentifié"], UNAUTHORIZED);  
        }
        else
        {
            return response()->json(['message' => "Erreur au niveau du serveur"], SERVER_ERROR);
        }
    }

}
