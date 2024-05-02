<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Critic;
use App\Repository\CriticRepositoryInterface;
use App\Http\Resources\CriticResource;
use Illuminate\Support\Facades\Auth;
use Exception;

class CriticController extends Controller
{
    private CriticRepositoryInterface $criticRepository;

    public function __construct(CriticRepositoryInterface $criticRepository) 
    {
        $this->criticRepository = $criticRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/critics/{filmId}",
     *     summary="Créer une critique pour un film",
     *     tags={"Critics"},
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *         name="filmId",
     *         in="path",
     *         required=true,
     *         description="ID du film pour lequel la critique est créée",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de la critique à créer",
     *         @OA\JsonContent(
     *             @OA\Property(property="score", type="integer", description="Score attribué à la critique"),
     *             @OA\Property(property="comment", type="string", description="Commentaire de la critique"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Critique créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID de la critique créée"),
     *             @OA\Property(property="user_id", type="integer", description="ID de l'utilisateur ayant créé la critique"),
     *             @OA\Property(property="film_id", type="integer", description="ID du film concerné par la critique"),
     *             @OA\Property(property="score", type="integer", description="Score attribué à la critique"),
     *             @OA\Property(property="comment", type="string", description="Commentaire de la critique"),
     *             @OA\Property(property="created_at", type="string", format="date-time", description="Date et heure de création de la critique"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", description="Date et heure de mise à jour de la critique"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Vous avez déjà critiqué ce film.")
     *         )
     *     )
     * )
     * )
    */
    public function store(Request $request, int $filmId)
    {
        $user = Auth::user();

        $json = $request->validate([
            'score' => 'required',
            'comment'=> 'required', 
        ]);

        $json['user_id'] = $user->id;
        $json['film_id'] = $filmId;

        $existingCritic = Critic::where('user_id', $user->id)
                                ->where('film_id', $filmId)
                                ->first();

        if ($existingCritic) {
            return response()->json(['error' => 'Vous avez déjà critiqué ce film.'], FORBIDDEN);
        }else{
            $critic = $this->criticRepository->create($json);
            return (new CriticResource($critic))->response()->setStatusCode(CREATED);
        }      
    }
}
