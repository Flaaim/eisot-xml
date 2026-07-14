<?php

declare(strict_types=1);

namespace App\Company\Command\FetchNameByInn;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d{10}(\d{2})?$/', message: 'INN must consist of 10 or 12 digits.')]
        public string $inn
    ) {}
}
