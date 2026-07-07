<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Subscription\GetPaymentStatus;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Subscription\Query\GetPaymentStatus\Handler;
use App\Subscription\Query\GetPaymentStatus\Query;
use DomainException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Validator $validator,
        private Security $security,
    ) {}

    #[Route('/v1/user/subscription/payment/{paymentId}', name: 'user.subscription.payment.status', methods: ['GET'])]
    public function __invoke(string $paymentId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $query = new Query(
            paymentId: $paymentId,
            userId: $userAdapter->getUserIdentifier(),
        );

        $this->validator->validate($query);

        try {
            $status = $this->handler->handle($query);
        } catch (DomainException) {
            return new JsonResponse(['message' => 'Payment not found.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'paymentId' => $status->paymentId,
            'status' => $status->status,
            'planId' => $status->planId,
        ]);
    }
}
