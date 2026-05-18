<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Token;

final class JoinConfirmationSender
{
    public function send(Email $email, string $token): void
    {

    }
}
