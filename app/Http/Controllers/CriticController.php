<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Critic;
use App\Repository\CriticRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CriticResource;

class CriticController extends Controller
{
    private CriticRepositoryInterface $criticRepository;

    public function __construct(CriticRepositoryInterface $criticRepository) 
    {
        $this->criticRepository = $criticRepository;
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        $filmId = $request->input('film_id');
        $existingCritic = Critic::where('user_id', $user->id)
                                ->where('film_id', $filmId)
                                ->first();

        if ($existingCritic) {
            return response()->json(['error' => 'Vous avez déjà critiqué un film.'], FORBIDDEN);
        }else{
            $critic = $this->criticRepository->create($request->all());
            return (new CriticResource($critic))->response()->setStatusCode(CREATED);
        }
        
    }

}
