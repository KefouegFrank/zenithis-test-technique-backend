<?php
// app/Http/Middleware/JwtMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = null;

        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            $response = response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 401);
        } catch (TokenInvalidException $e) {
            $response = response()->json([
                'success' => false,
                'message' => 'Token invalid'
            ], 401);
        } catch (JWTException $e) {
            $response = response()->json([
                'success' => false,
                'message' => 'Token absent'
            ], 401);
        }

        if ($response) {
            return $response;
        }

        return $next($request);
    }
}
