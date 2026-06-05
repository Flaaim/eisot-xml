<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\AddCompany;

use App\Company\Command\AddCompany\Command;
use App\Company\Command\AddCompany\Handler;
use App\Company\Entity\Company\Id;
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

    #[Route('/v1/companies', name: 'company.add', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->toArray();

        $id = Id::generate()->getValue();
        $name = (string)($body['name'] ?? '');
        $inn  = (string)($body['inn'] ?? '');

        $command = new Command($id, $name, $inn);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
    }
}
