<?php

declare(strict_types=1);

namespace AllyMailer\Wrapper;

use AllyMailer\Client\Configuration;

/**
 * Connection settings of the bundle: empty host - the default API host.
 */
final class ClientFactory
{
    /** The bundle and its version in the "platform" query parameter of the requests, before the SDK one. */
    public const PLATFORM = 'symfony.2.0.0';

    public static function configuration(?string $apiKey, ?string $host = null, float|int|string|null $timeout = null): Configuration
    {
        if (null === $apiKey || '' === $apiKey) {
            throw new \InvalidArgumentException('AllyMailer: set allymailer.api_key (ALLYMAILER_API_KEY).');
        }

        return new Configuration(
            $apiKey,
            null === $host || '' === $host ? Configuration::DEFAULT_BASE_URL : Configuration::baseUrlFor($host),
            null === $timeout || '' === $timeout ? 30.0 : (float)$timeout,
            platform: self::PLATFORM,
        );
    }
}
