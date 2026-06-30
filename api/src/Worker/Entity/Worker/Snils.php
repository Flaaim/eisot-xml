<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * СНИЛС гражданина РФ.
 *
 * Внутреннее представление: «XXX-XXX-XXX XX» (формат ЕИСОТ / XSD).
 * Пример: «112-233-445 95».
 *
 * Инварианты:
 *  - Ровно 11 цифр после нормализации.
 *  - Контрольное число по алгоритму ПФР (для номеров > 001-001-998).
 */
final readonly class Snils
{
    public const string PATTERN = '/^\d{3}-\d{3}-\d{3} \d{2}$/';

    private const string NINE_DIGITS_PATTERN = '/^\d{9}$/';

    /** Номера ≤ 001-001-998 не проверяются по контрольной сумме (ПФР). */
    private const int CHECKSUM_EXEMPT_MAX = 1_001_998;

    private function __construct(
        private string $value,
    ) {}

    /**
     * Создаёт СНИЛС из форматированной строки или 11 цифр подряд.
     */
    public static function fromString(string $value): self
    {
        $digits = self::normalize($value);

        Assert::length(
            $digits,
            11,
            'SNILS must contain exactly 11 digits.'
        );

        $formatted = self::toFormatted($digits);

        Assert::regex(
            $formatted,
            self::PATTERN,
            'SNILS must match format XXX-XXX-XXX XX (e.g. 112-233-445 95).'
        );

        self::assertValidChecksum($digits);

        return new self($formatted);
    }

    /**
     * Создаёт СНИЛС из 11 цифр (с разделителями или без).
     */
    public static function fromDigits(string $digits): self
    {
        return self::fromString($digits);
    }

    /**
     * Оставляет в строке только цифры (side-effect-free).
     */
    public static function normalize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * Приводит 11 цифр к формату ЕИСОТ «XXX-XXX-XXX XX».
     */
    public static function format(string $digits): string
    {
        $digits = self::normalize($digits);

        Assert::length(
            $digits,
            11,
            'SNILS must contain exactly 11 digits to format.'
        );

        return self::toFormatted($digits);
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

    /**
     * Алгоритм контрольного числа ПФР по первым 9 цифрам номера.
     */
    public static function calculateChecksum(string $nineDigits): int
    {
        $nineDigits = self::normalize($nineDigits);

        Assert::length(
            $nineDigits,
            9,
            'Exactly 9 digits are required to calculate SNILS checksum.'
        );

        Assert::regex(
            $nineDigits,
            self::NINE_DIGITS_PATTERN,
            'SNILS checksum input must contain only digits.'
        );

        $sum = 0;
        for ($i = 0; $i < 9; ++$i) {
            $sum += (int) $nineDigits[$i] * (9 - $i);
        }

        if ($sum < 100) {
            return $sum;
        }

        if ($sum === 100 || $sum === 101) {
            return 0;
        }

        $remainder = $sum % 101;

        return $remainder === 100 ? 0 : $remainder;
    }

    private static function assertValidChecksum(string $digits): void
    {
        Assert::length(
            $digits,
            11,
            'SNILS checksum validation requires exactly 11 digits.'
        );

        $serial = (int) substr($digits, 0, 9);
        $checksum = (int) substr($digits, 9, 2);

        if ($serial <= self::CHECKSUM_EXEMPT_MAX) {
            return;
        }

        $expected = self::calculateChecksum(substr($digits, 0, 9));

        if ($checksum !== $expected) {
            throw new InvalidArgumentException(
                sprintf('SNILS checksum is invalid. Expected %02d.', $expected)
            );
        }
    }

    private static function toFormatted(string $digits): string
    {
        return sprintf(
            '%s-%s-%s %s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 3),
            substr($digits, 9, 2),
        );
    }
}
