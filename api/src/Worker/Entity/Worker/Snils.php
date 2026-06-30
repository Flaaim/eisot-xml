<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * СНИЛС гражданина РФ.
 *
 * Формат: «XXX-XXX-XXX XX» (11 цифр, 3 дефиса, 1 пробел).
 * Пример: «112-233-445 95».
 *
 * Инварианты:
 *  - Строгое соответствие формату ЕИСОТ / XSD.
 *  - Контрольная сумма (для номеров > 001-001-998).
 */
final readonly class Snils
{
    public const string PATTERN = '/^\d{3}-\d{3}-\d{3} \d{2}$/';

    /** Номера ≤ этого значения не проверяются по контрольной сумме (ПФР). */
    private const int CHECKSUM_EXEMPT_MAX = 1_001_998;

    private function __construct(
        private string $value,
    ) {}

    public static function fromString(string $value): self
    {
        $normalized = self::normalize($value);

        Assert::regex(
            $normalized,
            self::PATTERN,
            'SNILS must match format XXX-XXX-XXX XX (e.g. 112-233-445 95).'
        );

        self::assertValidChecksum($normalized);

        return new self($normalized);
    }

    /**
     * Создаёт СНИЛС из 11 цифр (без разделителей).
     */
    public static function fromDigits(string $digits): self
    {
        $digits = preg_replace('/\D/', '', $digits) ?? '';

        Assert::length(
            $digits,
            11,
            'SNILS must contain exactly 11 digits.'
        );

        return self::fromString(self::formatDigits($digits));
    }

    /**
     * Приводит произвольный ввод к формату «XXX-XXX-XXX XX».
     * Если цифр меньше 11 — возвращает частично отформатированную строку (для UI).
     */
    public static function normalize(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        return self::formatDigits(substr($digits, 0, 11));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function formatDigits(string $digits): string
    {
        $part1 = substr($digits, 0, 3);
        $part2 = strlen($digits) > 3 ? substr($digits, 3, 3) : '';
        $part3 = strlen($digits) > 6 ? substr($digits, 6, 3) : '';
        $part4 = strlen($digits) > 9 ? substr($digits, 9, 2) : '';

        $formatted = $part1;

        if ($part2 !== '') {
            $formatted .= '-' . $part2;
        }
        if ($part3 !== '') {
            $formatted .= '-' . $part3;
        }
        if ($part4 !== '') {
            $formatted .= ' ' . $part4;
        }

        return $formatted;
    }

    private static function assertValidChecksum(string $formatted): void
    {
        $digits = preg_replace('/\D/', '', $formatted) ?? '';
        $serial = (int) substr($digits, 0, 9);
        $checksum = (int) substr($digits, 9, 2);

        if ($serial <= self::CHECKSUM_EXEMPT_MAX) {
            return;
        }

        $expected = self::calculateChecksum($digits);

        if ($checksum !== $expected) {
            throw new InvalidArgumentException(
                sprintf('SNILS checksum is invalid. Expected %02d.', $expected)
            );
        }
    }

    /**
     * Алгоритм контрольного числа ПФР.
     */
    public static function calculateChecksum(string $digits): int
    {
        $digits = preg_replace('/\D/', '', $digits) ?? '';
        Assert::minLength($digits, 9, 'At least 9 digits are required to calculate SNILS checksum.');

        $sum = 0;
        for ($i = 0; $i < 9; ++$i) {
            $sum += (int) $digits[$i] * (9 - $i);
        }

        if ($sum < 100) {
            return $sum;
        }

        if ($sum === 100 || $sum === 200) {
            return 0;
        }

        $remainder = $sum % 101;

        return $remainder === 100 ? 0 : $remainder;
    }
}
