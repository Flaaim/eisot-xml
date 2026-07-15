<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Entity\User\Email;
use App\Auth\Event\PasswordChanged;
use App\Auth\Service\PasswordChangeInfoSender;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class SendInfoOnChangedPasswordHandler
{
    public function __construct(
        private readonly PasswordChangeInfoSender $sender
    ) {}

    public function __invoke(PasswordChanged $event): void
    {
        $this->sender->send(new Email($event->email));
    }
}
