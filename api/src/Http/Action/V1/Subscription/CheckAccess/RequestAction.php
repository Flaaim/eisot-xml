<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Subscription\CheckAccess;

use App\OAuth\Entity\UserAdapter;
use App\Subscription\Query\CheckAccess\Handler;
use App\Subscription\Query\CheckAccess\Query;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Security $security,
    ) {}

    #[Route('/v1/user/subscription/access', name: 'user.subscription.check_access', methods: ['GET'])]
    public function __invoke(): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $access = $this->handler->handle(new Query($userAdapter->getUserIdentifier()));

        return new JsonResponse([
            'hasAccess' => $access->hasAccess,
            'plan' => $access->plan,
            'status' => $access->status,
            'periodStart' => $access->periodStart,
            'periodEnd' => $access->periodEnd,
        ]);
    }
}
