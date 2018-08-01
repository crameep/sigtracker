<?php

namespace App;

use Laravel\Socialite\Contracts\Provider;
use App\EveAccount;

class SocialAccountService
{
    public function setOrGetUser(Provider $provider)
    {
        $providerUser = $provider->user();
        $providerName = class_basename($provider);

        $account = EveAccount::whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {

            $account = new EveAccount([
                'provider_user_id' => $providerUser->getId(),
                'token' => $providerUser->token,
                'refreshToken' => $providerUser->refreshToken,
                'expiresIn' => $providerUser->expiresIn
            ]);

            $user = User::whereCharacterId($providerUser->getID())->first();

            if (!$user) {
                $user = User::create([
                    
                    'name' => $providerUser->getName(),
                    'character_id' => $providerUser->getId(),
                    'username' => strtolower(preg_replace('/\s+/', '_', $providerUser->name) . mt_rand(10, 100)),
                    'avatar' => $providerUser->avatar
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }

    }
}