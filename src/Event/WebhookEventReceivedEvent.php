<?php

declare(strict_types=1);

namespace AllyMailer\Wrapper\Event;

use AllyMailer\Api\Webhook\Model\WebhookCallbackEventsByUserItemEventsItem;
use AllyMailer\Api\Webhook\Model\WebhookCallbackEventsByUserItem;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * One event of a webhook notification (email status, spam block...): $event->eventName, $event->eventData,
 * the user/project of the event in $user.
 */
final class WebhookEventReceivedEvent extends Event
{
    public function __construct(
        public readonly WebhookCallbackEventsByUserItemEventsItem $event,
        public readonly WebhookCallbackEventsByUserItem $user,
    ) {
    }
}
