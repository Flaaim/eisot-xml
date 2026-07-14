<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\GetCompany;

use App\Company\Query\GetCompany\Handler;
use App\Company\Query\GetCompany\Query;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class RequestAction
{
    public function __construct(
        private readonly Security $security,
        private readonly Handler $handler,
    ) {}

    #[Route('/v1/companies/{id}', name: 'company.get', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function __invoke(string $id): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        $query = new Query($userId, $id);

        $company = $this->handler->handle($query);

        return new JsonResponse($company);
    }
}
