<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\FilmResource;
use App\Models\Film;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Repository\FilmRepositoryInterface;
use Illuminate\Support\Facades\Validator;


class FilmController extends Controller
{   
    private FilmRepositoryInterface $filmRepository;

    
    public function __construct(FilmRepositoryInterface $filmRepository) 
    {
        $this->filmRepository = $filmRepository;
    }

    public function create(Request $request)
    {
        if(Auth::check())
        {
            $user = Auth::user();
            $roleId = $user->role_id;
            if($roleId == ADMIN)
            {
                $rules = [
                    "title" => ['required', 'string'],
                    'release_year' => ['required', 'int'],
                    'length' => ['required', 'int'],
                    "description" =>['required', 'string'],
                    "rating" => ['required', 'int'],
                    "language_id"=> ['required', 'int'],
                    "special_features"=> ['required', 'string'],
                    "image"=> ['required', 'string']
                ];
        
                $validator = Validator::make($request->all(), $rules);
        
                if ($validator->fails()) {
                    
                    return response()->json(['message' => $validator->errors()], INVALID_DATA);
                }
                else
                {
                    $film = $this->filmRepository->create($request->all());
                    return (new FilmResource($film))->response()->setStatusCode(CREATED);
                }
            }
            else
            {
                return response()->json(['message' => "L'utilsateur n'a pas les permissions pour créer un film"], FORBIDDEN);  
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
    
    
    public function destroy(Request $request, int $film_id)
    {
        if(Auth::check())
        {
            $user = Auth::user();
            $roleId = $user->role_id;
            if($roleId == ADMIN)
            {
                $filmToDelete = $this->filmRepository->getById($film_id);
                //Film::findOrFail($film_id);
                $critics = $filmToDelete->critics;

        
                foreach($critics as $critic)
                {
                    $this->filmRepository->delete($critic->id);
                    //$critic->delete();
                }
                $this->filmRepository->delete($filmToDelete->id);
                //$filmToDelete->delete();

                return response()->json(['message' => "Suppression réussie"], NO_CONTENT);
            }
            else
            {
                return response()->json(['message' => "L'utilsateur n'a pas les permissions pour supprimer ce film"], FORBIDDEN); 
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

