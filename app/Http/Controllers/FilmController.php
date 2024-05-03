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

    /**
     * @OA\Post(
     *     path="/api/films",
     *     summary="Création d'un film",
     *     tags={"Films"},
     *     security={{"Token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Film data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Nom du film"
     *                 ),
     *                 @OA\Property(
     *                     property="release_year",
     *                     type="integer",
     *                     format="year",
     *                     example=2004
     *                 ),
     *                 @OA\Property(
     *                     property="length",
     *                     type="integer",
     *                     example=120
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="Description du film"
     *                 ),
     *                 @OA\Property(
     *                     property="rating",
     *                     type="integer",
     *                     example=5
     *                 ),
     *                 @OA\Property(
     *                     property="language_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="special_features",
     *                     type="string",
     *                     example="Caractéristiques spéciales du film"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     example="Lien vers l'image du film"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Création réussie"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Non authentifié"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="L'utilisateur n'a pas les permissions requises",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Non authentifié"
     *             )
     *         )
     *     )
     * )
     */
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
    
    /**
     * @OA\Put(
     *     path="/api/films/{id}",
     *     summary="Mise à jour d'un film",
     *     tags={"Films"},
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du film à mettre à jour",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du film à mettre à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Monster Inc"),
     *             @OA\Property(property="release_year", type="integer", example=2001),
     *             @OA\Property(property="length", type="integer", example=121),
     *             @OA\Property(property="description", type="string", example="Yayy"),
     *             @OA\Property(property="rating", type="string", example="G"),
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="special_features", type="string", example="languages"),
     *             @OA\Property(property="image", type="string", example="bla"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Film mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="release_year", type="integer"),
     *             @OA\Property(property="length", type="integer"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="rating", type="string"),
     *             @OA\Property(property="language_id", type="integer"),
     *             @OA\Property(property="special_features", type="string"),
     *             @OA\Property(property="image", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas les autorisations nécessaires pour cette action.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Film non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Le film n'existe pas.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role_id !== ADMIN) {
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
            'special_features' => 'required|string',
            'image' => 'required|string',
        ]);
    
        $movie->update($validatedData);
    
        return response()->json($movie, OK);
    }   
    
    /**
     * @OA\Delete(
     *     path="/api/films/{id}",
     *     summary="Delete a film",
     *     tags={"Films"},
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="L'id du film à supprimer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Le film a été bien supprimé "
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Le film n'a pas été trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *    @OA\Response(
     *         response=403,
     *         description="L'utilisateur n'a pas les permissions requises pour la suppression"
     *     )
     * )
     */
    public function destroy(Request $request, int $film_id)
    {
        if(Auth::check())
        {
            $user = Auth::user();
            $roleId = $user->role_id;
            if($roleId == ADMIN)
            {
                $filmToDelete = $this->filmRepository->getById($film_id);
                $critics = $filmToDelete->critics;

        
                foreach($critics as $critic)
                {
                    $this->filmRepository->delete($critic->id);
                }
                $this->filmRepository->delete($filmToDelete->id);

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

