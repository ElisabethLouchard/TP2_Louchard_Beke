<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\FilmResource;
use App\Models\Film;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Repository\FilmRepositoryInterface;


class FilmController extends Controller
{   
    private FilmRepositoryInterface $filmRepository;

    
    public function __construct(FilmRepositoryInterface $filmRepository) 
    {
        $this->filmRepository = $filmRepository;
    }

    public function create(Request $request)
    {

        //convertir maybe en repository
        if(Auth::check())
        {
            $user = Auth::user();

            //Mettre ca dans une fonction?
            $roleId = $user->role;
            if($roleId == ADMIN)
            {
                $film = $this->filmRepository->create($request->all());
                return (new FilmResource($film))->response()->setStatusCode(CREATED);
            }
            else
            {
                return response()->json(['message' => "L'utilsateur n'a pas les permissions pour créer un film"], UNAUTHORIZED);  
            }
            
        }
        else if(Auth::check() ==false)
        {
            return response()->json(['message' => "L'utilsateur n'est pas authentifié"], UNAUTHORIZED);  
        }
        else
        {
            return response()->json(['message' => "Erreur au niveau du serveur"], SERVER_ERROR); 
        }
    }   
}

