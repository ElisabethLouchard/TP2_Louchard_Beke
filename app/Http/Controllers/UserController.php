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

    /**
     * @OA\Get(
     *     path="/api/users/{user_id}",
     *     summary="Afficher les détails d'un utilisateur",
     *     tags={"Users"},
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur à afficher",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="login", type="string", description="Login de l'utilisateur"),
     *             @OA\Property(property="email", type="string", format="email", description="Adresse email de l'utilisateur"),
     *             @OA\Property(property="last_name", type="string", description="Nom de famille de l'utilisateur"),
     *             @OA\Property(property="first_name", type="string", description="Prénom de l'utilisateur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="L'utilisateur n'est pas authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="L'utilisateur n'a pas les permissions pour afficher cet utilisateur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur au niveau du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur au niveau du serveur")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/api/users/{id}",
     *     summary="Mise à jour du mot de passe de l'utilisateur",
     *     tags={"Users"},
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur dont le mot de passe doit être mis à jour",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Nouveau mot de passe de l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="new_password", type="string", format="password", minLength=6, description="Nouveau mot de passe", example="fortnight"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", minLength=6, description="Confirmation du nouveau mot de passe", example="fortnight"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Message indiquant que le mot de passe a été mis à jour")
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
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Utilisateur non trouvé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le nouveau mot de passe et sa confirmation doivent correspondre."),
     *             @OA\Property(property="errors", type="object", description="Liste des erreurs de validation détaillées", example={"new_password_confirmation": {"Le nouveau mot de passe et sa confirmation doivent correspondre."}})
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = $this->userRepository->getById($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], NOT_FOUND);
        }
    
        if (Auth::user()->id !== (int)$id) {
            return response()->json(['error' => 'Vous n\'avez pas les autorisations nécessaires pour cette action.'], FORBIDDEN);
        }
    
        $validatedData = $request->validate([
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ]);
        
        $user->password = bcrypt($validatedData['new_password']);
        $user->save();
    
        return response()->json(['message' => 'Mot de passe mis à jour avec succès.'], OK);
    }

}
