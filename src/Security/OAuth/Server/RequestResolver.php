<?php

declare(strict_types=1);

namespace Api\Security\OAuth\Server;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
final class RequestResolver implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onRequestResolve',];
    }

    /**
     * @param AuthorizationRequestResolveEvent $event
     */
    public function onRequestResolve(AuthorizationRequestResolveEvent $event): void
    {
        $user = $event->getUser();

        if (null === $user) {
            return;
        }

        $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
    }
}
