<?php

declare(strict_types=1);

namespace AllyMailer\Wrapper\Routing;

use AllyMailer\Wrapper\Controller\WebhookController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route "allymailer_webhook" at the configured allymailer.webhook.path:
 *
 * ```yaml
 * # config/routes/allymailer.yaml
 * allymailer_webhook:
 *     resource: .
 *     type: allymailer_webhook
 * ```
 */
final class WebhookRouteLoader extends Loader
{
    public function __construct(private readonly string $path)
    {
        parent::__construct();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $routes = new RouteCollection();
        $routes->add('allymailer_webhook', new Route($this->path, ['_controller' => WebhookController::class], methods: ['GET', 'HEAD', 'POST']));

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'allymailer_webhook' === $type;
    }
}
