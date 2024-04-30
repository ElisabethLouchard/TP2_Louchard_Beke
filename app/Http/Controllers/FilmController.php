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
        if(Auth::check())
        {
            $user = Auth::user();
            $roleId = $user->role_id;
            if($roleId == ADMIN)
            {
                $film = $this->filmRepository->create($request->all());
                return (new FilmResource($film))->response()->setStatusCode(CREATED);
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
    
    public function update(Request $request, $id)
    {
        try {

            if (Auth::user()->role_id !== 1) {
                return response()->json(['error' => 'Vous n\'avez pas les autorisations nécessaires pour cette action.'], FORBIDDEN);
            }
    
            $movie = $this->filmRepository->getById($id);
            if (!$movie) {
                return response()->json(['error' => 'Le film n\'existe pas.'], NOT_FOUND);
            }
    
            $validatedData = $request->validate([
                'title' => 'required|string',
                'release_year' => 'required|integer',
                'length' => 'required|integer',
                'description' => 'required|string',
                'rating' => 'required|string',
                'language_id' => 'required|integer',
                'special_features' => 'required|array',
                'image' => 'required|string',
            ]);
    
            $movie->update($validatedData);
    
            return response()->json($movie, OK);
        } catch (\Exception $ex) {
            return response()->json(['error' => 'Erreur serveur.'], SERVER_ERROR);
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
                $filmToDelete = Film::findOrFail($film_id);
                $critics = $filmToDelete->critics;

                foreach($critics as $critic)
                {
                    $critic->delete();
                }
                $filmToDelete->delete();

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

