<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\GetCompanyStats;

use App\Company\Query\GetCompanyStats\Handler;
use App\Company\Query\GetCompanyStats\Query;
use App\OAuth\Entity\UserAdapter;
use DomainException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP Action для получения статистики компании.
 */
final readonly class RequestAction
{
    public function __construct(
        private Security $security,
        private Handler $handler,
    ) {}

    #[Route('/v1/companies/{companyId}/stats', name: 'company.stats', methods: ['GET'])]
    public function __invoke(string $companyId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        try {
            $query = new Query($companyId, $userId);
            $stats = $this->handler->handle($query);

            return new JsonResponse($stats);
        } catch (DomainException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
