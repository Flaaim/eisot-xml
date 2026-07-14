<?php

declare(strict_types=1);

namespace App\Company\Exception;

use RuntimeException;
use Throwable;

final class RemoteException extends RuntimeException implements RemoteExceptionInterface
{
    public function __construct(
        string $message = 'Ошибка при выполнении внешнего запроса',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
