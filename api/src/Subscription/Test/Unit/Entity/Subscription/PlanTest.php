<?php

declare(strict_types=1);

namespace App\Subscription\Test\Unit\Entity\Subscription;

use App\Subscription\Entity\Subscription\Plan;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class PlanTest extends TestCase
{
    public function testBasicAllowsFirstCompanyOnly(): void
    {
        self::assertTrue(Plan::BASIC->canAddMoreCompanies(0));
        self::assertFalse(Plan::BASIC->canAddMoreCompanies(1));
        self::assertFalse(Plan::BASIC->canAddMoreCompanies(2));
    }

    public function testExtendedAllowsUnlimitedCompanies(): void
    {
        self::assertTrue(Plan::EXTENDED->canAddMoreCompanies(0));
        self::assertTrue(Plan::EXTENDED->canAddMoreCompanies(1));
        self::assertTrue(Plan::EXTENDED->canAddMoreCompanies(100));
    }
}
