<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\ArchiveCompany;

use App\Company\Command\ArchiveCompany\Command;
use App\Company\Command\ArchiveCompany\Handler;
use App\Infrastructure\Http\Validator\Validator;
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
 */
final class RequestAction
{
    public function __construct(
        private readonly Handler   $handler,
        private readonly Validator $validator,
    ) {}

    #[Route('/v1/companies/{id}', name: 'company.archive', methods: ['DELETE'])]
    public function __invoke(Request $request, string $id): Response
    {
        $command = new Command(id: $id);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
