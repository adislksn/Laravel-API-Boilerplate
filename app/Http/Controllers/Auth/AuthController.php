<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\GeneralException;
use App\Helpers\TokenGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\GeneralResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request) {
        
        // Check if user credentials are valid
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            throw new GeneralException( 
                'Invalid credentials',
                401
            );
        }

        // Handle user email verification
        $user = User::where('email', $request->email)->first();
        $data['refresh_token'] = TokenGenerator::refreshToken($user);
        if(!$user->email_verified_at){
            return new GeneralResource(
                401,
                'Email not verified',
                $data
            );
        }

        // Generate access token
        $data['access_token'] = TokenGenerator::accessToken($user);
        $data['user'] = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        return new GeneralResource(
            200,
            'Login successful',
            $data
        );
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request) {
        // Revoke user token
        $request->user()->currentAccessToken()->delete();

        return new GeneralResource(
            200,
            'Logout successful',
            []
        );
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request) {
        // Handle user email verification
        $user = $request->user();
        if(!$user->email_verified_at){
            throw new GeneralException(
                'Email not verified',
                401
            );
        }

        // Generate access token
        $data['access_token'] = TokenGenerator::accessToken($user);
        return new GeneralResource(
            200,
            'Token refreshed',
            $data
        );
    }

    /**
     * Get current user data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request) {
        return new GeneralResource(
            200,
            'User data',
            $request->user()
        );
    }
}
