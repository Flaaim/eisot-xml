<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

/**
 * Статус компании (активна или архивирована).
 */
enum CompanyStatus: string
{
    case ACTIVE = 'ACTIVE';
    case ARCHIVED = 'ARCHIVED';
}
