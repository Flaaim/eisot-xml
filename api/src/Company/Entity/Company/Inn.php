<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Inn
{
    private const LENGTH_10 = 10;
    private const LENGTH_12 = 12;

    /** @var list<int> */
    private const CHECKSUM_COEF_10 = [2, 4, 10, 3, 5, 9, 4, 6, 8];

    /** @var list<int> */
    private const CHECKSUM_COEF_12_FIRST = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

    /** @var list<int> */
    private const CHECKSUM_COEF_12_SECOND = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

    public function __construct(
        private string $value
    ) {
        Assert::regex($value, '/^\d{10}(\d{2})?$/', 'INN must consist of 10 or 12 digits.');
        self::assertValidChecksum($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isLegal(): bool
    {
        return self::LENGTH_10 === \strlen($this->value);
    }

    public function isIndividual(): bool
    {
        return self::LENGTH_12 === \strlen($this->value);
    }

    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function assertValidChecksum(string $value): void
    {
        if (self::LENGTH_10 === \strlen($value)) {
            $expected = self::calculateCheckDigit($value, self::CHECKSUM_COEF_10);

            if ($expected !== (int)$value[9]) {
                throw new InvalidArgumentException('INN checksum is invalid.');
            }

            return;
        }

        if (self::LENGTH_12 === \strlen($value)) {
            $firstExpected = self::calculateCheckDigit($value, self::CHECKSUM_COEF_12_FIRST);
            $secondExpected = self::calculateCheckDigit($value, self::CHECKSUM_COEF_12_SECOND);

            if ($firstExpected !== (int)$value[10] || $secondExpected !== (int)$value[11]) {
                throw new InvalidArgumentException('INN checksum is invalid.');
            }
        }
    }

    /**
     * @param list<int> $coefficients
     */
    private static function calculateCheckDigit(string $digits, array $coefficients): int
    {
        $sum = 0;

        foreach ($coefficients as $index => $coefficient) {
            $sum += (int)$digits[$index] * $coefficient;
        }

        return ($sum % 11) % 10;
    }
}
