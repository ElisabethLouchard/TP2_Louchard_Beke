<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
	//Copiez votre register complet ici + la route pour y accéder

    /**
     * @OA\Post(
     *     path="/api/signup",
     *     tags={"Auth"},
     *     summary="Create a new user",
     *     operationId="createUser",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     example="taytay13"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     example="cardigan"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     example="taylorswift@gmail.com"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                     example="Swift"
     *                 ),
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                     example="Taylor"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required',
            'password' => 'required',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'last_name' => 'required',
            'first_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], BAD_REQUEST);
        }

        $user = new User();
        $user->login = $request->input('login');
        $user->password = bcrypt($request->input('password'));
        $user->email = $request->input('email');
        $user->last_name = $request->input('last_name');
        $user->first_name = $request->input('first_name');
        $user->save();

        return response()->json(['user' => $user], CREATED); 
    }

    /**
     * @OA\Post(
     *     path="/api/signin",
     *     tags={"Auth"},
     *     summary="Authenticate user",
     *     operationId="authenticateUser",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     example="taytay13"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="cardigan"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur d'authentification")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|max:50',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = $request->user();
            $token = $user->createToken('Connexion Token')->plainTextToken;
            return response()->json(['token' => $token], CREATED); 
        } else {
            return response()->json(['error' => 'Erreur d\'authentification'], UNAUTHORIZED); 
        }
    }

    /**
    * @OA\Get(
    *     path="/api/signout",
    *     summary="Déconnexion de l'utilisateur",
    *     tags={"Auth"},
    *     security={{"Token":{}}},
    *     @OA\Response(
    *         response=204,
    *         description="Déconnexion réussie"
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
    *     )
    * )
    * @OA\SecurityScheme(
    *   securityScheme="Token",
    *   type="http",
    *   scheme="bearer",
    *   bearerFormat="JWT"
    * )
    */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user != null) {
            $user->tokens()->delete();
        } else {
            return response()->json(['error' => 'Non authentifié'], UNAUTHORIZED);
        }
    
        return response()->json(null, NO_CONTENT);
    }
}