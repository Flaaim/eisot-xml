<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Worker\RegisterWorker;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Worker\Command\RegisterWorker\Command;
use App\Worker\Command\RegisterWorker\Handler;
use App\Worker\Entity\Worker\WorkerId;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
    ) {}

    #[Route('/v1/companies/{companyId}/workers', name: 'worker.register', methods: ['POST'])]
    public function __invoke(Request $request, string $companyId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        $body = $request->toArray();

        $workerId = WorkerId::generate()->getValue();

        $command = new Command(
            workerId: $workerId,
            companyId: $companyId,
            userId: $userId,
            lastName: (string)($body['lastName'] ?? ''),
            firstName: (string)($body['firstName'] ?? ''),
            middleName: isset($body['middleName']) ? (string)$body['middleName'] : null,
            profession: (string)($body['profession'] ?? ''),
            isForeigner: (bool)($body['isForeigner'] ?? false),
            snils: isset($body['snils']) ? (string)$body['snils'] : null,
            citizenship: isset($body['citizenship']) ? (string)$body['citizenship'] : null,
            foreignSnils: isset($body['foreignSnils']) ? (string)$body['foreignSnils'] : null,
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(['id' => $workerId], Response::HTTP_CREATED);
    }
}
