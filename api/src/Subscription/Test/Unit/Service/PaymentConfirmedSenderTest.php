<?php

declare(strict_types=1);

namespace App\Subscription\Test\Unit\Service;

use App\Subscription\Service\PaymentConfirmedSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @internal
 * @coversNothing
 */
final class PaymentConfirmedSenderTest extends TestCase
{
    public function testSuccess(): void
    {
        $email = 'test@mail.ru';
        $durationDays = 5;
        $template = 'subscribe/payment/confirm.html.twig';
        $twig = new Environment(new ArrayLoader([
            $template => "<p>Успешная подписка: адрес - {$email}</p>",
        ]));
        $symfonyEmail = new Email()
            ->to($email)
            ->subject('Успешный платеж')
            ->html($twig->render($template, ['email' => $email]));

        $mailer = $this->createMock(MailerInterface::class);

        $mailer->expects(self::once())->method('send')
            ->willReturnCallback(static function (Email $message) use ($symfonyEmail): int {
                self::assertEquals($symfonyEmail->getTo(), $message->getTo());
                self::assertEquals($symfonyEmail->getSubject(), $message->getSubject());
                self::assertStringContainsString((string)$symfonyEmail->getHtmlBody(), (string)$message->getHtmlBody());
                return 1;
            });

        $sender = new PaymentConfirmedSender(
            $mailer,
            $twig
        );
        $sender->send($email, $durationDays);
    }

    public function testError(): void
    {
        $email = 'test@mail.ru';
        $durationDays = 5;
        $template = 'subscribe/payment/confirm.html.twig';
        $twig = new Environment(new ArrayLoader([
            $template => "<p>Успешная подписка: адрес - {$email}</p>",
        ]));

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send')->willThrowException(new TransportException('Exception'));

        $sender = new PaymentConfirmedSender($mailer, $twig);
        self::expectException(TransportException::class);
        self::expectExceptionMessage('Exception');
        $sender->send($email, $durationDays);
    }
}
