<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Admin\GetStats;

use App\Admin\Query\GetSubscriptionStats\Handler;
use App\Admin\Query\GetSubscriptionStats\Query;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Статистика User Subscription для Admin-панели.
 *
 * GET /v1/admin/stats
 */
#[Route('/v1/admin/stats', name: 'admin.stats', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
    ) {}

    public function __invoke(): Response
    {
        $stats = $this->handler->handle(new Query());

        return new JsonResponse([
            'totalUsers' => $stats->totalUsers,
            'registrationsLast30Days' => $stats->registrationsLast30Days,
            'activeSubscriptions' => $stats->activeSubscriptions,
            'activeBasicPlan' => $stats->activeBasicPlan,
            'activeExtendedPlan' => $stats->activeExtendedPlan,
            'activeSubscriptionsLast30Days' => $stats->activeSubscriptionsLast30Days,
        ], Response::HTTP_OK);
    }
}
