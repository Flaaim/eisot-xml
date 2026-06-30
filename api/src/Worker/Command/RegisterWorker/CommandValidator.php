<?php

declare(strict_types=1);

namespace App\Worker\Command\RegisterWorker;

use App\Worker\Entity\Worker\Snils;
use InvalidArgumentException;

/**
 * Прикладная валидация команды RegisterWorker.
 *
 * Выполняется в Handler до создания агрегата и генерации доменного события.
 * Дублирует бизнес-правила SnilsInfo, но возвращает понятные ошибки на уровне команды.
 */
final class CommandValidator
{
    public function validate(Command $command): void
    {
        if (!$command->isForeigner) {
            $this->validateCitizen($command);

            return;
        }

        $this->validateForeigner($command);
    }

    private function validateCitizen(Command $command): void
    {
        if ($command->snils === null || trim($command->snils) === '') {
            throw new \DomainException('SNILS is required for a citizen of Russia.');
        }

        if ($command->citizenship !== null && trim($command->citizenship) !== '') {
            throw new \DomainException('Citizenship must be empty for a citizen of Russia.');
        }

        if ($command->foreignSnils !== null && trim($command->foreignSnils) !== '') {
            throw new \DomainException('Foreign SNILS must be empty for a citizen of Russia.');
        }

        try {
            Snils::fromString($command->snils);
        } catch (InvalidArgumentException $e) {
            throw new \DomainException($e->getMessage());
        }
    }

    private function validateForeigner(Command $command): void
    {
        if ($command->snils !== null && trim($command->snils) !== '') {
            throw new \DomainException('Standard SNILS must be empty for a foreign worker.');
        }

        if ($command->citizenship === null || trim($command->citizenship) === '') {
            throw new \DomainException('Citizenship is required for a foreign worker.');
        }

        if ($command->foreignSnils !== null && trim($command->foreignSnils) !== '') {
            if (strlen($command->foreignSnils) > 30) {
                throw new \DomainException('Foreign SNILS must not exceed 30 characters.');
            }
        }
    }
}
