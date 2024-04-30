<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\LanguageResource;
use App\Models\User;
use App\Models\Language;
use Illuminate\Support\Facades\Validator;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository) 
    {
        $this->userRepository = $userRepository;
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
