<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\GetCompanies;

use App\Company\Query\GetCompanies\Handler;
use App\Company\Query\GetCompanies\Query;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/companies', name: 'company.list', methods: ['GET'])]
final class RequestAction
{
    public function __construct(
        private readonly Handler  $handler,
        private readonly Security $security,
    ) {}

    public function __invoke(): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        $query = new Query($userId);

        $companies = $this->handler->handle($query);

        return new JsonResponse($companies, Response::HTTP_OK);
    }
}
