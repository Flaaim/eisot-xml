<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Yookassa;

use Symfony\Component\HttpFoundation\Request;

/**
 * Проверка IP-адреса входящего webhook ЮKassa.
 *
 * @see https://yookassa.ru/developers/using-api/webhooks
 */
final class YookassaWebhookGuard
{
    /** @var list<string> */
    private const array ALLOWED_CIDRS = [
        '185.71.76.0/27',
        '185.71.77.0/27',
        '77.75.153.0/25',
        '77.75.156.11/32',
        '77.75.156.35/32',
        '77.75.154.128/25',
        '2a02:5180::/32',
    ];

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private string $environment = 'prod',
    ) {}

    public function isAllowed(Request $request): bool
    {
        if ('test' === $this->environment) {
            return true;
        }

        $clientIp = $request->getClientIp();

        if (null === $clientIp || '' === $clientIp) {
            return false;
        }

        foreach (self::ALLOWED_CIDRS as $cidr) {
            if ($this->ipMatchesCidr($clientIp, $cidr)) {
                return true;
            }
        }

        return false;
    }

    private function ipMatchesCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        $parts = explode('/', $cidr, 2);
        $subnet = $parts[0];
        $maskBits = (int)($parts[1] ?? 32);

        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && false !== filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);

            if (false === $ipLong || false === $subnetLong) {
                return false;
            }

            $mask = -1 << (32 - $maskBits);

            return ($ipLong & $mask) === ($subnetLong & $mask);
        }

        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && false !== filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);

            if (false === $ipBin || false === $subnetBin) {
                return false;
            }

            $fullBytes = intdiv($maskBits, 8);
            $remainingBits = $maskBits % 8;

            if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
                return false;
            }

            if (0 === $remainingBits) {
                return true;
            }

            $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

            return (\ord($ipBin[$fullBytes]) & $mask) === (\ord($subnetBin[$fullBytes]) & $mask);
        }

        return false;
    }
}
