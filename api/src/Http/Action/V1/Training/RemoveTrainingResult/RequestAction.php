<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Training\RemoveTrainingResult;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Training\Command\RemoveTrainingResult\Command;
use App\Training\Command\RemoveTrainingResult\Handler;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
    ) {}

    #[Route('/v1/companies/{companyId}/{recordId}', name: 'company.training_records.remove', methods: ['DELETE'])]
    public function __invoke(string $companyId, string $recordId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        $command = new Command(
            id: $recordId,
            userId: $userId,
            companyId: $companyId,
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
