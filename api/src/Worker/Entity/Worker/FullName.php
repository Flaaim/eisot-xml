<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Webmozart\Assert\Assert;

/**
 * ФИО работника.
 *
 * lastName и firstName — обязательны, middleName — опционально (иностранцы могут не иметь отчества).
 */
final readonly class FullName
{
    private function __construct(
        private string $lastName,
        private string $firstName,
        private ?string $middleName,
    ) {}

    public static function create(string $lastName, string $firstName, ?string $middleName = null): self
    {
        Assert::notEmpty($lastName, 'Last name must not be empty.');
        Assert::maxLength($lastName, 100, 'Last name must not exceed 100 characters.');

        Assert::notEmpty($firstName, 'First name must not be empty.');
        Assert::maxLength($firstName, 100, 'First name must not exceed 100 characters.');

        if (null !== $middleName && '' !== $middleName) {
            Assert::maxLength($middleName, 100, 'Middle name must not exceed 100 characters.');
        } else {
            $middleName = null;
        }

        return new self($lastName, $firstName, $middleName);
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * Полное имя в формате «Фамилия Имя Отчество».
     */
    public function getFull(): string
    {
        $parts = [$this->lastName, $this->firstName];

        if (null !== $this->middleName) {
            $parts[] = $this->middleName;
        }

        return implode(' ', $parts);
    }

    public function isEqualTo(self $other): bool
    {
        return $this->lastName === $other->lastName
            && $this->firstName === $other->firstName
            && $this->middleName === $other->middleName;
    }
}
