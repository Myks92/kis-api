<?php

declare(strict_types=1);

namespace Api\Security\OAuth\Server;

use Myks92\User\Model\User\Service\PasswordHasher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\UserResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
final class UserResolver implements EventSubscriberInterface
{
    /**
     * @var UserProviderInterface
     */
    private UserProviderInterface $userProvider;
    /**
     * @var PasswordHasher
     */
    private PasswordHasher $hasher;

    /**
     * @param UserProviderInterface $userProvider
     * @param PasswordHasher $hasher
     */
    public function __construct(UserProviderInterface $userProvider, PasswordHasher $hasher)
    {
        $this->userProvider = $userProvider;
        $this->hasher = $hasher;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [OAuth2Events::USER_RESOLVE => 'onUserResolve',];
    }

    /**
     * @param UserResolveEvent $event
     */
    public function onUserResolve(UserResolveEvent $event): void
    {
        $user = $this->userProvider->loadUserByUsername($event->getUsername());

        if (null === $user) {
            return;
        }

        if (!$user->getPassword()) {
            return;
        }

        if (!$this->hasher->validate($event->getPassword(), $user->getPassword())) {
            return;
        }

        $event->setUser($user);
    }
}
