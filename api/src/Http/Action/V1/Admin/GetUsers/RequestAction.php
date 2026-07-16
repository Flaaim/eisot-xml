<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Admin\GetUsers;

use App\Admin\Query\GetUsersList\Handler;
use App\Admin\Query\GetUsersList\Query;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Список User для Admin-панели.
 *
 * GET /v1/admin/users?page=1&limit=20&email=&subscriptionStatus=
 */
#[Route('/v1/admin/users', name: 'admin.users.list', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Validator $validator,
    ) {}

    public function __invoke(Request $request): Response
    {
        $query = new Query(
            page: max(1, (int)$request->query->get('page', 1)),
            limit: min(100, max(1, (int)$request->query->get('limit', 20))),
            email: $this->nullableString($request->query->get('email')),
            subscriptionStatus: $this->nullableString($request->query->get('subscriptionStatus')),
        );

        $this->validator->validate($query);

        $result = $this->handler->handle($query);

        return new JsonResponse([
            'items' => array_map(
                static fn ($item): array => [
                    'id' => $item->id,
                    'email' => $item->email,
                    'status' => $item->status,
                    'role' => $item->role,
                    'createdAt' => $item->createdAt,
                    'activeSubscriptionPlan' => $item->activeSubscriptionPlan,
                    'subscriptionStatus' => $item->subscriptionStatus,
                    'companiesCount' => $item->companiesCount,
                ],
                $result->items,
            ),
            'total' => $result->total,
            'page' => $result->page,
            'limit' => $result->limit,
        ], Response::HTTP_OK);
    }

    private function nullableString(mixed $value): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}
