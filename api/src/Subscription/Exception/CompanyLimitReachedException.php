<?php

declare(strict_types=1);

namespace App\Subscription\Exception;

use DomainException;

/**
 * Лимит активных компаний для текущего тарифного Plan исчерпан.
 */
final class CompanyLimitReachedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Достигнут лимит компаний для вашего тарифа. Обновите тариф до Extended.');
    }
}
