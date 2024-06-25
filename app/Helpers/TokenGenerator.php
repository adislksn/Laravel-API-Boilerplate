<?php
namespace App\Helpers;

use App\Enums\TokenAbility;
use Carbon\Carbon;

class TokenGenerator
{
    public static function accessToken(object $user)
    {
        return $user->createToken('access_token', 
        [TokenAbility::ACCESS_API->value], Carbon::now()
        ->addMinutes(config('sanctum.ac_expiration')))
        ->plainTextToken;
    }

    public static function refreshToken(object $user)
    {
        return $user->createToken('refresh_token', 
        [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()
        ->addMinutes(config('sanctum.rt_expiration')))
        ->plainTextToken;
    }
}