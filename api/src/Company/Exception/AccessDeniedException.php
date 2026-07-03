<?php

declare(strict_types=1);

namespace App\Company\Exception;

use RuntimeException;

/**
 * Выбрасывается, когда пользователь пытается изменить
 * компанию, которая ему не принадлежит.
 *
 * HTTP-слой должен транслировать это исключение в 403 Forbidden.
 */
final class AccessDeniedException extends RuntimeException
{
    public function __construct(string $message = 'Access denied: you do not own this company.')
    {
        parent::__construct($message);
    }
}
