<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\ArchiveCompany;

use App\Company\Command\ArchiveCompany\Command;
use App\Company\Command\ArchiveCompany\Handler;
use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Архивирование компании (мягкое удаление).
 *
 * REST: DELETE /v1/companies/{id}
 * Успех: 204 No Content.
 * Конфликт (уже архивирована / не найдена): 409 Conflict (через DomainExceptionSubscriber).
 * Нет прав: 403 Forbidden (через AccessDeniedException).
 */
final class RequestAction
{
    public function __construct(
        private readonly Handler   $handler,
        private readonly Validator $validator,
        private readonly Security  $security,
    ) {}

    #[Route('/v1/companies/{id}', name: 'company.archive', methods: ['DELETE'])]
    public function __invoke(Request $request, string $id): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new Command(
            id:     $id,
            userId: $userAdapter->getUserIdentifier(),
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
