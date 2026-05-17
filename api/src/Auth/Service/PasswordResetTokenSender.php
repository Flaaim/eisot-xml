<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Entity\User\Email;

final class PasswordResetTokenSender
{
    public function send(Email $email, string $token): void {}
}
