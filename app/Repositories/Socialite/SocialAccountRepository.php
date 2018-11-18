<?php

namespace App\Repositories\Socialite;

use Laravel\Socialite\Contracts\User as ProviderUser;
use App\Models\SocialAccount;
use App\Repositories\User\UserInterface;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use App\Models\User;

class SocialAccountRepository extends BaseRepository
{

    public function __construct(SocialAccount $socialAccount) 
    {
        parent::__construct($socialAccount);
    }

    public function createOrGetUser($providerUser, $provider)
    {
        $account = $provider == config('settings.framgia')
            ? $this->where('provider', $provider)->where('email', $providerUser->getEmail())->first()
            : $this->where('provider', $provider)->where('provider_user_id', $providerUser->getId())->first();
        $user = null;

        if ($account) {
            $user = app(UserInterface::class)->find($account->user_id);
            $data = [
                'email' => $providerUser->getEmail(),
                'name' => $providerUser->getName(),
                'image' => $providerUser->getAvatar(),
            ];

            if ($provider == config('settings.framgia')) {
                $data['birthday'] = $providerUser->getBirthday();
                $data['phone'] = $providerUser->getPhoneNumber();

                if ($providerUser->getGender() == 'male') {
                    $data['gender'] = config('users.gender.male');
                } elseif ($providerUser->getGender() == 'female') {
                    $data['gender'] = config('users.gender.female');
                } else {
                    $data['gender'] = config('users.gender.other_gender');
                }
            }

            if ($providerUser->getEmail()) {
                app(UserInterface::class)->newQuery(new User());
                $check = app(UserInterface::class)
                    ->where('email', $providerUser->getEmail())
                    ->where('id', '<>', $user->id)
                    ->exists();
                $data = $check ? array_except($data, ['email']) : $data;
            }

            $account->fill($data);

            if ($account->getDirty()) {
                $account->save();
                $user->fill($data);
                $user->save();
            }

            return $user;
        }

        if ($providerUser->getEmail()) {
            $user = app(UserInterface::class)->where('email', $providerUser->getEmail())->first();
        }

        if (!$user) {
            $newUser = [
                'email' => $providerUser->getEmail(),
                'name' => $providerUser->getName(),
                'password' => '',
                'image' => $providerUser->getAvatar(),
                'level' => config('users.level.user'),
                'status' => config('users.status.active'),
            ];

            if ($provider == config('settings.framgia')) {
                $newUser['birthday'] = \Carbon\Carbon::parse($providerUser->getBirthday())->toDateString();
                $newUser['phone'] = $providerUser->getPhoneNumber();

                if ($providerUser->getGender() == 'male') {
                    $newUser['gender'] = config('users.gender.male');
                } elseif ($providerUser->getGender() == 'female') {
                    $newUser['gender'] = config('users.gender.female');
                } else {
                    $newUser['gender'] = config('users.gender.other_gender');
                }
            }

            $user = app(UserInterface::class)->firstOrCreate($newUser);
        }

        $account = $this->create([
            'user_id' => $user->id,
            'provider_user_id' => $providerUser->getId(),
            'provider' => $provider,
            'email' => $providerUser->getEmail(),
            'name' => $providerUser->getName(),
            'image' => $providerUser->getAvatar(),
        ]);

        return $user;
    }
}
