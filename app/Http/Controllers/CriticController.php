<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Critic;
use App\Repository\CriticRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
            $critic = new Critic();
            $critic->user_id = $user->id;
            $critic->film_id = $filmId;
            $critic->score = $request->input('score');
            $critic->comment = $request->input('comment');
            $critic->save();
            return response()->json($critic, CREATED);
        }
        
    }

}
