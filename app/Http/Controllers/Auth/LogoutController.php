<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;


class LogoutController extends Controller
{
    public function __invoke()
    {
        if (EnsureFrontendRequestsStateful::fromFrontend(request()))
        {
            Auth::guard('web')->logout();

            request()->session()->invalidate();
        
            request()->session()->regenerateToken();
        } else {
            // Revoke token
        }
    }
}
