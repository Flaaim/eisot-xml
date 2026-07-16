<?php

declare(strict_types=1);

namespace App\Admin\Query\GetUsersList;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Query
{
    public function __construct(
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Range(min: 1, max: 100)]
        public int $limit = 20,
        #[Assert\Length(max: 255)]
        public ?string $email = null,
        #[Assert\Choice(choices: ['active', 'none', 'expired'], message: 'Invalid subscription status filter.')]
        public ?string $subscriptionStatus = null,
    ) {}
}
