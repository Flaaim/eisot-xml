<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Training\GetRegistryRecords;

use App\OAuth\Entity\UserAdapter;
use App\Training\Query\GetRegistryRecords\Handler;
use App\Training\Query\GetRegistryRecords\Query;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/companies/{companyId}/training-records', name: 'company.training_records.list', methods: ['GET'])]
final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Security $security,
    ) {}

    public function __invoke(string $companyId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        $query = new Query(companyId: $companyId, userId: $userId);

        $records = $this->handler->handle($query);

        return new JsonResponse($records, Response::HTTP_OK);
    }
}
