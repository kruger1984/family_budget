<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthAction
{
    public function execute(SocialiteUser $socialUser, string $provider): User
    {
        $data = [
            'name' => $socialUser->getName(),
            'avatar' => $socialUser->getAvatar(),
        ];

        if ($provider === 'google') {
            $data['google_id'] = $socialUser->getId();
        } elseif ($provider === 'apple') {
            $data['apple_id'] = $socialUser->getId();
        }

        return User::query()->updateOrCreate(
            ['email' => $socialUser->getEmail()],
            $data
        );
    }
}
