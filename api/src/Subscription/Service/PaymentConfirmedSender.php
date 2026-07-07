<?php

declare(strict_types=1);

namespace App\Subscription\Service;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Twig\Environment;

final class PaymentConfirmedSender
{
    public const string TEMPLATE = 'subscribe/payment/confirm.html.twig';

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public function send(string $email, string $ended): void
    {
        $message = new SymfonyEmail()
            ->subject('Подписка активирована')
            ->to($email)
            ->html($this->twig->render(self::TEMPLATE, ['email' => $email, 'ended' => $ended]));
        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException($e->getMessage());
        }
    }
}
