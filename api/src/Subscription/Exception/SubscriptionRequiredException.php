<?php

declare(strict_types=1);

namespace App\Subscription\Exception;

/**
 * Подписка требуется для формирования RegistrySet XML.
 */
final class SubscriptionRequiredException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Active User Subscription is required to export RegistrySet XML.');
    }
}
