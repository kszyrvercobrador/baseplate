<?php

namespace App\Http\ApiControllers\Auth;

use Illuminate\Http\Request;
use App\Http\ApiControllers\ApiController;
use App\Http\Resources\UserResource;

class UserController extends ApiController
{

    /**
     * Returns current user details
     */
    public function __invoke(Request $request)
    {
        return UserResource::make($request->user());
    }
}
