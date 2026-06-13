<?php

declare(strict_types=1);

namespace App\Worker\Exception;

/**
 * Выбрасывается, когда пользователь пытается зарегистрировать
 * работника в компании, которая ему не принадлежит.
 *
 * HTTP-слой транслирует в 403 Forbidden.
 */
final class AccessDeniedException extends \RuntimeException
{
    public function __construct(string $message = 'Access denied: you do not own this company.')
    {
        parent::__construct($message);
    }
}
