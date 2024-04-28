<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\FilmResource;
use App\Models\Film;
use App\Repository\FilmRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class FilmController extends Controller
{   
    private FilmRepositoryInterface $filmRepository;

    
    public function __construct(FilmRepositoryInterface $filmRepository) 
    {
        $this->filmRepository = $filmRepository;
    }
    
    public function update(Request $request, $id)
    {
        try {

            if (Auth::user()->role_id !== 1) {
                return response()->json(['error' => 'Vous n\'avez pas les autorisations nécessaires pour cette action.'], FORBIDDEN);
            }
    
            $movie = Film::find($id);
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
}

