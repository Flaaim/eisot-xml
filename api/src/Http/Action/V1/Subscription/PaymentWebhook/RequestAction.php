<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Subscription\PaymentWebhook;

use App\Infrastructure\Payment\Yookassa\YookassaWebhookGuard;
use App\Subscription\Command\ConfirmPayment\Command as ConfirmPaymentCommand;
use App\Subscription\Command\ConfirmPayment\Handler as ConfirmPaymentHandler;
use App\Subscription\Command\FailPayment\Command as FailPaymentCommand;
use App\Subscription\Command\FailPayment\Handler as FailPaymentHandler;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final readonly class RequestAction
{
    public function __construct(
        private YookassaWebhookGuard $webhookGuard,
        private ConfirmPaymentHandler $confirmPaymentHandler,
        private FailPaymentHandler $failPaymentHandler,
        private LoggerInterface $logger,
    ) {}

    #[Route('/v1/subscription/payment/webhook', name: 'subscription.payment.webhook', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
//        if (!$this->webhookGuard->isAllowed($request)) {
//            $this->logger->warning('Rejected YooKassa webhook from untrusted IP.', [
//                'clientIp' => $request->getClientIp(),
//            ]);
//
//            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
//        }

        try {
            $payload = $request->toArray();
        } catch (Throwable) {
            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        $event = (string)($payload['event'] ?? '');
        $object = $payload['object'] ?? null;

        if (!\is_array($object)) {
            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        $externalId = (string)($object['id'] ?? '');
        if ('' === $externalId) {
            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        try {
            if ('payment.succeeded' === $event) {
                $this->confirmPaymentHandler->handle(new ConfirmPaymentCommand($externalId));
            } elseif ('payment.canceled' === $event) {
                $this->failPaymentHandler->handle(new FailPaymentCommand($externalId));
            }
        } catch (DomainException $exception) {
            $this->logger->error('YooKassa webhook processing failed.', [
                'event' => $event,
                'externalId' => $externalId,
                'exception' => $exception,
            ]);

            return new Response('Not Found', Response::HTTP_NOT_FOUND);
        } catch (Throwable $exception) {
            $this->logger->error('YooKassa webhook processing failed.', [
                'event' => $event,
                'externalId' => $externalId,
                'exception' => $exception,
            ]);

            return new Response('Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('OK', Response::HTTP_OK);
    }
}
