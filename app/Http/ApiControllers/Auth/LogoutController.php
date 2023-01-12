<?php

namespace App\Http\ApiControllers\Auth;

use Illuminate\Http\Request;
use App\Http\ApiControllers\ApiController;

class LogoutController extends ApiController
{
    /**
     * Revoke the token that was used to authenticate the current request
     */
    public function __invoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }
}
