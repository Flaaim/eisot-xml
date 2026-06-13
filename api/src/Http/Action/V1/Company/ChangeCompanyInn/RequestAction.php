<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\ChangeCompanyInn;

use App\Company\Command\ChangeCompanyInn\Command;
use App\Company\Command\ChangeCompanyInn\Handler;
use App\Infrastructure\Http\Validator\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler   $handler,
        private readonly Validator $validator,
    ) {}

    #[Route('/v1/companies/{id}/inn', name: 'company.change_inn', methods: ['PATCH'])]
    public function __invoke(Request $request, string $id): Response
    {
        $body = $request->toArray();

        $command = new Command(
            id:  $id,
            inn: (string)($body['inn'] ?? ''),
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse([], Response::HTTP_OK);
    }
}
