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

    public function send(string $email, int $durationDays): void
    {
        $message = new SymfonyEmail()
            ->subject('Успешный платеж')
            ->to($email)
            ->html($this->twig->render(self::TEMPLATE, ['email' => $email, 'durationDays' => $durationDays]));
        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new TransportException($e->getMessage());
        }
    }
}
