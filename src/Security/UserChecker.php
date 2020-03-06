<?php

declare(strict_types=1);

namespace Api\Security;

use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * @param UserInterface $identity
     */
    public function checkPreAuth(UserInterface $identity): void
    {
        if (!$identity instanceof UserIdentity) {
            return;
        }

        if (!$identity->isActive()) {
            $exception = new DisabledException('User account is disabled.');
            $exception->setUser($identity);
            throw $exception;
        }
    }

    /**
     * @param UserInterface $identity
     */
    public function checkPostAuth(UserInterface $identity): void
    {
        if (!$identity instanceof UserIdentity) {
            return;
        }
    }
}
