<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\UserRepositoryInterface;
use Exception;
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
        try {
            $user = $this->userRepository->getById($id);
            $userRequest = $request->user()->id;

            if($id != $userRequest){
                return response()->json(['error' => 'Mauvais utilisateur.'], FORBIDDEN);
            }
            
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié.'], NOT_FOUND);
            }
    
            $validatedData = $request->validate([
                'new_password' => 'required|min:6',
                'new_password_confirmation' => 'required|same:new_password',
            ]);
    
            $user->password = bcrypt($validatedData['new_password']);
            $user->save();
    
            return response()->json(['message' => 'Mot de passe mis à jour avec succès.'], OK);
    
        } catch (Exception $ex) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du mot de passe.'], SERVER_ERROR);
        }
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
