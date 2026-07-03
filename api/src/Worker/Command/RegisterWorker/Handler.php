<?php

declare(strict_types=1);

namespace App\Worker\Command\RegisterWorker;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id as CompanyEntityId;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Infrastructure\Doctrine\Flusher;
use App\Worker\Entity\Worker\CompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\Worker;
use App\Worker\Entity\Worker\WorkerId;
use App\Worker\Entity\Worker\WorkerRepository;
use App\Worker\Exception\AccessDeniedException;

/**
 * Обработчик команды RegisterWorker.
 *
 * Ответственность:
 *  1. Проверка существования компании и прав владельца
 *  2. Трансформация примитивов → Value Objects (инварианты SnilsInfo)
 *  3. Создание агрегата Worker через фабричный метод
 *  4. Сохранение + flush
 */
final class Handler
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly WorkerRepository $workers,
        private readonly Flusher $flusher,
        private readonly CommandValidator $commandValidator,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Command $command): void
    {
        // 1. Проверяем что компания существует
        $company = $this->companies->get(new CompanyEntityId($command->companyId));

        // 2. Проверяем что компания принадлежит текущему пользователю
        if (!$company->getUserId()->isEqualTo(new CompanyUserId($command->userId))) {
            throw new AccessDeniedException();
        }

        // 3. Прикладная валидация команды (до создания агрегата и доменного события)
        $this->commandValidator->validate($command);

        // 4. Создаём Value Objects (инварианты SnilsInfo проверяются автоматически)
        $workerId   = new WorkerId($command->workerId);
        $companyId  = new CompanyId($command->companyId);
        $fullName   = FullName::create($command->lastName, $command->firstName, $command->middleName);
        $profession = Profession::fromString($command->profession);
        $snilsInfo  = SnilsInfo::fromPrimitives(
            $command->isForeigner,
            $command->snils,
            $command->citizenship,
            $command->foreignSnils,
        );

        // 5. Создаём агрегат
        $worker = Worker::register($workerId, $companyId, $fullName, $profession, $snilsInfo);

        // 6. Сохраняем
        $this->workers->add($worker);
        $this->flusher->flush();
    }
}
