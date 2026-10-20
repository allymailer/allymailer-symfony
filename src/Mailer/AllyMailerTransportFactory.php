<?php

declare(strict_types=1);

namespace AllyMailer\Wrapper\Mailer;

use AllyMailer\Client\ApiClient;
use AllyMailer\Client\Configuration;
use AllyMailer\Wrapper\ClientFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * MAILER_DSN:
 * - allymailer+api://default             the API client of the bundle (allymailer.api_key, allymailer.host);
 * - allymailer+api://API_KEY@default     own API key, default host;
 * - allymailer+api://API_KEY@cp.allymailer.com  own API key and host (datacenter).
 */
final class AllyMailerTransportFactory extends AbstractTransportFactory
{
    public function __construct(
        private readonly ApiClient $apiClient,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($dispatcher, null, $logger);
    }

    public function create(Dsn $dsn): TransportInterface
    {
        if (!\in_array($dsn->getScheme(), $this->getSupportedSchemes(), true)) {
            throw new UnsupportedSchemeException($dsn, 'allymailer', $this->getSupportedSchemes());
        }

        $client = $this->apiClient;
        $key = $dsn->getUser();
        if (null !== $key && '' !== $key) {
            $host = $dsn->getHost();
            $baseUrl = 'default' === $host ? Configuration::DEFAULT_BASE_URL : Configuration::baseUrlFor($host . (null !== $dsn->getPort() ? ':' . $dsn->getPort() : ''));
            $client = new ApiClient(ClientFactory::configuration($key, $baseUrl, $dsn->getOption('timeout')));
        }

        return new AllyMailerTransport($client, $this->dispatcher, $this->logger);
    }

    protected function getSupportedSchemes(): array
    {
        return ['allymailer+api', 'allymailer'];
    }
}
