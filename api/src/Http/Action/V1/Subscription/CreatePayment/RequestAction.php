<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Subscription\CreatePayment;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Subscription\Command\CreatePayment\Command;
use App\Subscription\Command\CreatePayment\Handler;
use App\Subscription\Service\PaymentGatewayException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Validator $validator,
        private Security $security
    ) {}

    #[Route('/v1/user/subscription/payment', name: 'user.subscription.payment.create', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        try {
            /** @var UserAdapter|null $userAdapter */
            $userAdapter = $this->security->getUser();

            if (!$userAdapter instanceof UserAdapter) {
                return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
            }

            try {
                $body = $request->toArray();
            } catch (Throwable) {
                return new JsonResponse(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
            }

            $returnUrl = (string)($body['returnUrl'] ?? '');
            if ('' === $returnUrl) {
                $returnUrl = (string)($body['return_url'] ?? '');
            }

            $command = new Command(
                planId: (string)($body['planId'] ?? ''),
                durationDays: (int)($body['durationDays'] ?? 0),
                userId: $userAdapter->getUserIdentifier(),
                returnUrl: $returnUrl,
            );

            $this->validator->validate($command);

            try {
                $result = $this->handler->handle($command);
            } catch (PaymentGatewayException $exception) {
                return new JsonResponse(
                    ['message' => 'Payment gateway is temporarily unavailable.'],
                    Response::HTTP_BAD_GATEWAY,
                );
            }

            return new JsonResponse([
                'paymentId' => $result->paymentId,
                'confirmationUrl' => $result->confirmationUrl,
            ], Response::HTTP_CREATED);
        } catch (Throwable $debugException) {
            return new JsonResponse([
                'debug_error' => true,
                'class' => \get_class($debugException),
                'message' => $debugException->getMessage(),
                'file' => $debugException->getFile(),
                'line' => $debugException->getLine(),
                'trace' => explode("\n", $debugException->getTraceAsString()), // Стек вызовов
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
