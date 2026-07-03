<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\RestoreCompany;

use App\Company\Command\RestoreCompany\Command;
use App\Company\Command\RestoreCompany\Handler;
use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Восстановление компании из архива.
 *
 * REST: POST /v1/companies/{companyId}/restore
 * Успех: 204 No Content.
 * Конфликт (уже активна / не найдена): 409 Conflict.
 * Нет прав: 403 Forbidden.
 */
final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Validator $validator,
        private Security $security,
    ) {}

    #[Route('/v1/companies/{companyId}/restore', name: 'company.restore', methods: ['POST'])]
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
