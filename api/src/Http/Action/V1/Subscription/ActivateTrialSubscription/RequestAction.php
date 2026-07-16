<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Subscription\ActivateTrialSubscription;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Subscription\Command\ActivateTrialSubscription\Command;
use App\Subscription\Command\ActivateTrialSubscription\Handler;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Активация Trial Subscription (3 дня, один раз).
 *
 * POST /v1/user/subscription/trial/activate
 */
final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Validator $validator,
        private Security $security,
    ) {}

    #[Route('/v1/user/subscription/trial/activate', name: 'user.subscription.trial.activate', methods: ['POST'])]
    public function __invoke(): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new Command(userId: $userAdapter->getUserIdentifier());
        $this->validator->validate($command);

        $subscriptionId = $this->handler->handle($command);

        return new JsonResponse(['id' => $subscriptionId->getValue()], Response::HTTP_CREATED);
    }
}
