<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Training\AttachRegistryNumber;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Training\Command\AttachRegistryNumber\Command;
use App\Training\Command\AttachRegistryNumber\Handler;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler   $handler,
        private readonly Validator $validator,
        private readonly Security  $security,
    ) {}

    #[Route('/v1/training-records/{recordId}/registry-number', name: 'training.attach_registry_number', methods: ['PATCH'])]
    public function __invoke(Request $request, string $recordId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();
        $body   = $request->toArray();

        $command = new Command(
            recordId:       $recordId,
            userId:         $userId,
            registryNumber: (string)($body['registryNumber'] ?? ''),
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
