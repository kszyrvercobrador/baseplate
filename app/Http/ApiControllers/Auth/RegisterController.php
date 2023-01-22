<?php

namespace App\Http\ApiControllers\Auth;

use App\Events\UserRegistered;
use App\Models\User;
use App\Http\ApiControllers\ApiController;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;

class RegisterController extends ApiController
{
    /**
     * Regiter user using email and password
     * Create access token after registration
     */
    public function __invoke(RegisterRequest $request)
    {
        $user = User::create([
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'password' => bcrypt($request->input('password')),
        ]);

        $token = $user->createToken($request->userAgent())->plainTextToken;

        UserRegistered::dispatch($user);

        return response()->json([
            'data' => [
                'access_token' => $token,
                'user' => UserResource::make($user),
            ]
        ]);
    }
}
