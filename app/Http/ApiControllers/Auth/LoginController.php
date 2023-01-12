<?php

namespace App\Http\ApiControllers\Auth;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\ApiControllers\ApiController;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;

class LoginController extends ApiController
{
    /**
     * Authenticate user using email and password
     */
    public function __invoke(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($request->userAgent())->plainTextToken;

        return response()->json([
            'data' => [
                'access_token' => $token,
                'user' => UserResource::make($user),
            ]
        ]);
    }
}
