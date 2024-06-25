<?php

namespace App\Http\Controllers\Auth\JWT;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\GeneralResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JWTAuthController extends Controller
{
    /**
     * Login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        // Auth validation
        $credentials = $request->only('email', 'password');
        if (!Auth::guard('api')->attempt($credentials)) {
            throw new GeneralException(
                'Invalid credentials',
                401
            );
        }

        // Get user data
        $user = User::where('email', $request->email)->first();
        
        // User data
        $data['user'] = [
            'name' => $user->name,
            'email' => $user->email,
            // 'role' => $user->Role->role_name
        ];

        // Generate token
        $token = auth('api')->login($user);
        $data['access_token'] = $token;

        // Handle email verification
        if (!$user->email_verified_at) {
            throw new GeneralException(
                'Email not verified',
                401,
                $data
            );
        }

        // return response
        return new GeneralResource(
            200,
            'Login successful',
            $data
        );
    }

    /**
     * Get current user data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if (!auth()->user()) {
            throw new GeneralException(
                'Unauthorized user',
                404
            );
        }
        return new GeneralResource(
            200,
            'User data',
            auth()->user()
        );
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
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
    public function refreshToken(Request $request)
    {
        try {
            /** @var Illuminate\Auth\AuthManager */
            $auth = auth('api');
            $data = $auth->refresh(true, true);
            return new GeneralResource(
                200,
                'Token refreshed',
                $data
            );
        } catch (Exception $e) {
            throw new GeneralException(
                'Token refresh failed',
                401
            );
        }
    }

    // public function register(RegisterRequest $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // check user role and create user
    //         $role_uuid = Roles::where('role_name', 'User')->first()->uuid;
    //         $user = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'role_uuid' => $role_uuid,
    //             'password' => Hash::make($request->password)
    //         ]);

    //         // handle user not created
    //         if (!$user) {
    //             return (new PostResource(
    //                 'failed',
    //                 'User is not created',
    //                 []
    //             ))->response()->setStatusCode(400);
    //         }

    //         // generate JWT token
    //         $token = auth('api')->attempt(
    //             [
    //                 'email' => $request->email,
    //                 'password' => $request->password
    //             ]
    //         );

    //         // send email verification
    //         $url = env('APP_FE_URL') . '/auth/verification?token=' . $token;
    //         Mail::to($request->email)->send(new UserVerifyMail($url));

    //         $data['user'] = $user->name;
    //         $data['access_token'] = auth('api')->login($user);

    //         DB::commit();
    //         return (new PostResource(
    //             'success',
    //             'User is created successfully, please verify your email',
    //             $data
    //         ))->response()->setStatusCode(201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return (new PostResource(
    //             'failed',
    //             'User is not created',
    //             []
    //         ))->response()->setStatusCode(400);
    //     }
    // }
}
