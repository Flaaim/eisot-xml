<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\RemoveCompany;

use App\Company\Command\RemoveCompany\Command;
use App\Company\Command\RemoveCompany\Handler;
use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Безвозвратное удаление архивированной компании.
 *
 * REST: DELETE /v1/companies/{companyId}
 * Успех: 204 No Content.
 */
final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
    ) {}

    #[Route('/v1/companies/{companyId}', name: 'company.remove', methods: ['DELETE'])]
    public function __invoke(string $companyId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new Command(
            id: $companyId,
            userId: $userAdapter->getUserIdentifier(),
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
