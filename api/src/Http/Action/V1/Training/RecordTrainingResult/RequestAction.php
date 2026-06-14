<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Training\RecordTrainingResult;

use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use App\Training\Command\RecordTrainingResult\Command;
use App\Training\Command\RecordTrainingResult\Handler;
use App\Training\Entity\Record\Id;
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

    #[Route('/v1/workers/{workerId}/training-records', name: 'training.record', methods: ['POST'])]
    public function __invoke(Request $request, string $workerId): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();
        $body   = $request->toArray();

        $id = Id::generate()->getValue();

        $command = new Command(
            id:             $id,
            workerId:       $workerId,
            userId:         $userId,
            program:        (string)($body['program'] ?? ''),
            result:         (string)($body['result'] ?? ''),
            date:           (string)($body['date'] ?? ''),
            protocolNumber: (string)($body['protocolNumber'] ?? ''),
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
    }
}
