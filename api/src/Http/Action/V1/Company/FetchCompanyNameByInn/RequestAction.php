<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Company\FetchCompanyNameByInn;

use App\Company\Command\FetchNameByInn\Command;
use App\Company\Command\FetchNameByInn\Handler;
use App\Company\Exception\RemoteException;
use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Security $security,
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/v1/companies/suggestions', name: 'company.get_name_by_inn', methods: ['GET'])]
    public function __invoke(#[MapQueryParameter] string $inn = ''): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $command = new Command($inn);

        $this->validator->validate($command);
        try {
            $name = $this->handler->handle($command);
            return new JsonResponse(['title' => $name]);
        } catch (RemoteException $e) {
            $this->logger->warning('Dadata API error: ' . $e->getMessage(), ['exception' => $e]);

            return new JsonResponse(
                ['message' => 'Сервис проверки контрагентов временно недоступен. Попробуйте позже.'],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }
}
