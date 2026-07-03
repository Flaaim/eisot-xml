<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use DomainException;
use Webmozart\Assert\Assert;

/**
 * Инкапсулирует логику СНИЛС с учётом гражданства (ЕИСОТ колонки 8, 9, 10).
 *
 * Инварианты:
 *  - Гражданин РФ: обязателен стандартный СНИЛС, поля иностранца пусты.
 *  - Иностранец:   стандартный СНИЛС пуст, гражданство обязательно, СНИЛС иностранца опционально.
 *
 * Создаётся только через named constructors forCitizen() / forForeigner().
 */
final readonly class SnilsInfo
{
    private function __construct(
        private bool $isForeigner,
        private ?Snils $snils,
        private ?string $citizenship,
        private ?string $foreignSnils,
    ) {}

    /**
     * Гражданин РФ: стандартный СНИЛС обязателен.
     */
    public static function forCitizen(Snils $snils): self
    {
        return new self(
            isForeigner: false,
            snils: $snils,
            citizenship: null,
            foreignSnils: null,
        );
    }

    /**
     * Иностранец: гражданство обязательно, СНИЛС иностранца опционально.
     */
    public static function forForeigner(string $citizenship, ?string $foreignSnils = null): self
    {
        Assert::notEmpty($citizenship, 'Citizenship is required for a foreign worker.');
        Assert::maxLength($citizenship, 100, 'Citizenship must not exceed 100 characters.');

        if (null !== $foreignSnils) {
            Assert::notEmpty($foreignSnils, 'Foreign SNILS must not be empty if provided.');
            Assert::maxLength($foreignSnils, 30, 'Foreign SNILS must not exceed 30 characters.');
        }

        return new self(
            isForeigner: true,
            snils: null,
            citizenship: $citizenship,
            foreignSnils: $foreignSnils,
        );
    }

    /**
     * Фабричный метод из примитивов (используется Handler'ом).
     *
     * @throws DomainException при нарушении инварианта
     */
    public static function fromPrimitives(
        bool $isForeigner,
        ?string $snils,
        ?string $citizenship,
        ?string $foreignSnils,
    ): self {
        if (!$isForeigner) {
            if (null === $snils || '' === $snils) {
                throw new DomainException('SNILS is required for a citizen of Russia.');
            }
            if (null !== $citizenship && '' !== $citizenship) {
                throw new DomainException('Citizenship must be empty for a citizen of Russia.');
            }
            if (null !== $foreignSnils && '' !== $foreignSnils) {
                throw new DomainException('Foreign SNILS must be empty for a citizen of Russia.');
            }

            return self::forCitizen(Snils::fromString($snils));
        }

        // Иностранец
        if (null !== $snils && '' !== $snils) {
            throw new DomainException('Standard SNILS must be empty for a foreign worker.');
        }
        if (null === $citizenship || '' === $citizenship) {
            throw new DomainException('Citizenship is required for a foreign worker.');
        }

        return self::forForeigner(
            $citizenship,
            (null !== $foreignSnils && '' !== $foreignSnils) ? $foreignSnils : null,
        );
    }

    public function isForeigner(): bool
    {
        return $this->isForeigner;
    }

    public function getSnils(): ?Snils
    {
        return $this->snils;
    }

    public function getCitizenship(): ?string
    {
        return $this->citizenship;
    }

    public function getForeignSnils(): ?string
    {
        return $this->foreignSnils;
    }
}
