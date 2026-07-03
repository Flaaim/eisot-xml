<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Subscription\ActivateSubscription;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Subscription\Command\ActivateSubscription\Command;
use App\Subscription\Command\ActivateSubscription\Handler;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Validator $validator,
        private Security $security,
    ) {}

    #[Route('/v1/user/subscription/activate', name: 'user.subscription.activate', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $body = $request->toArray();
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        $command = new Command(
            planId: (string) ($body['planId'] ?? ''),
            durationDays: (int) ($body['durationDays'] ?? 0),
            userId: $userAdapter->getUserIdentifier(),
        );

        $this->validator->validate($command);

        $subscriptionId = $this->handler->handle($command);

        return new JsonResponse(['id' => $subscriptionId->getValue()], Response::HTTP_CREATED);
    }
}
