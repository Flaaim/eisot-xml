<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Admin\GetPayments;

use App\Admin\Query\GetPaymentsList\Handler;
use App\Admin\Query\GetPaymentsList\Query;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Список платежей User Subscription для Admin-панели.
 *
 * GET /v1/admin/payments?page=1&limit=20
 */
#[Route('/v1/admin/payments', name: 'admin.payments.list', methods: ['GET'])]
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
        );

        $this->validator->validate($query);

        $result = $this->handler->handle($query);

        return new JsonResponse([
            'items' => array_map(
                static fn ($item): array => [
                    'id' => $item->id,
                    'userId' => $item->userId,
                    'userEmail' => $item->userEmail,
                    'plan' => $item->plan,
                    'status' => $item->status,
                    'amountValue' => $item->amountValue,
                    'amountCurrency' => $item->amountCurrency,
                    'createdAt' => $item->createdAt,
                    'confirmedAt' => $item->confirmedAt,
                ],
                $result->items,
            ),
            'total' => $result->total,
            'page' => $result->page,
            'limit' => $result->limit,
        ], Response::HTTP_OK);
    }
}
