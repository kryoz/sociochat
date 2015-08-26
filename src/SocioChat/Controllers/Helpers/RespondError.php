<?php

namespace SocioChat\Controllers\Helpers;

use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Response\ErrorResponse;

class RespondError
{
    public static function make(User $user, $errors = null)
    {
        $response = (new ErrorResponse())
            ->setErrors(
                is_array($errors) ? $errors : [$errors ?: $user->getLang()->getPhrase('RequiredActionNotSpecified')]
            )
            ->setChannelId($user->getChannelId());

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify();
    }
}
