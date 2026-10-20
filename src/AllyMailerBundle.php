<?php

declare(strict_types=1);

namespace AllyMailer\Wrapper;

use AllyMailer\Client\ApiClient;
use AllyMailer\Client\Configuration;
use AllyMailer\AllyMailerClient;
use AllyMailer\Wrapper\Controller\WebhookController;
use AllyMailer\Wrapper\Mailer\AllyMailerTransportFactory;
use AllyMailer\Wrapper\Routing\WebhookRouteLoader;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * AllyMailer API: AllyMailerClient / ApiClient / Configuration services, the "allymailer+api" Symfony Mailer transport and the
 * webhook endpoint (route type "allymailer_webhook").
 */
final class AllyMailerBundle extends AbstractBundle
{
    protected string $extensionAlias = 'allymailer';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_key')->info('User or project API key.')->defaultValue('%env(ALLYMAILER_API_KEY)%')->end()
                ->scalarNode('host')->info('API host (cp.allymailer.com) or a full base URL, empty: https://cp.allymailer.com/ua/transactional/api/v1.')->defaultValue('%env(default::ALLYMAILER_HOST)%')->end()
                ->scalarNode('timeout')->info('Request timeout, seconds.')->defaultValue('%env(default::ALLYMAILER_TIMEOUT)%')->end()
                ->arrayNode('webhook')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->info('Path of the webhook endpoint.')->defaultValue('/allymailer/webhook')->end()
                        ->scalarNode('key')->info('API key the notifications are signed with, empty: api_key.')->defaultValue('%env(default::ALLYMAILER_WEBHOOK_KEY)%')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array{api_key: string, host: string|null, timeout: string|float|null, webhook: array{path: string, key: string|null}} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set('allymailer.configuration', Configuration::class)
            ->factory([ClientFactory::class, 'configuration'])
            ->args([$config['api_key'], $config['host'], $config['timeout']]);
        $services->alias(Configuration::class, 'allymailer.configuration');

        $services->set('allymailer.api_client', ApiClient::class)
            ->args([service('allymailer.configuration')]);
        $services->alias(ApiClient::class, 'allymailer.api_client');

        $services->set('allymailer.client', AllyMailerClient::class)
            ->args([service('allymailer.configuration')]);
        $services->alias(AllyMailerClient::class, 'allymailer.client')->public();

        $services->set('allymailer.mailer.transport_factory', AllyMailerTransportFactory::class)
            ->args([
                service('allymailer.api_client'),
                service('event_dispatcher')->nullOnInvalid(),
                service('logger')->nullOnInvalid(),
            ])
            ->tag('mailer.transport_factory');

        $services->set(WebhookController::class)
            ->args([
                $config['webhook']['key'],
                $config['api_key'],
                service('event_dispatcher'),
            ])
            ->tag('controller.service_arguments')
            ->public();

        $services->set('allymailer.webhook.route_loader', WebhookRouteLoader::class)
            ->args([$config['webhook']['path']])
            ->tag('routing.loader');
    }
}
