<?php

declare(strict_types=1);

namespace Api\Security;

use Api\ReadModel\User\AuthView;
use Api\ReadModel\User\UserFetcher;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function count;
use function get_class;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserFetcher
     */
    private UserFetcher $users;

    /**
     * @param UserFetcher $users
     */
    public function __construct(UserFetcher $users)
    {
        $this->users = $users;
    }

    /**
     * @param string $username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->loadUser($username);
        return self::identityByUser($user, $username);
    }

    /**
     * @param $username
     *
     * @return AuthView
     */
    private function loadUser($username): AuthView
    {
        $chunks = explode(':', $username);

        if (count($chunks) === 2 && $user = $this->users->findForAuthByNetwork($chunks[0], $chunks[1])) {
            return $user;
        }

        if ($user = $this->users->findForAuthByEmail($username)) {
            return $user;
        }

        throw new UsernameNotFoundException('');
    }

    /**
     * @param AuthView $user
     * @param string $username
     *
     * @return UserIdentity
     */
    private static function identityByUser(AuthView $user, string $username): UserIdentity
    {
        return new UserIdentity(
            $user->id,
            $user->email ?: $username,
            $user->password_hash ?: '',
            $user->name ?: $username,
            $user->role,
            $user->status
        );
    }

    /**
     * @param UserInterface $identity
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $identity): UserInterface
    {
        if (!$identity instanceof UserIdentity) {
            throw new UnsupportedUserException('Invalid user class ' . get_class($identity));
        }

        $user = $this->loadUser($identity->getUsername());

        return self::identityByUser($user, $identity->getUsername());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return $class instanceof UserIdentity;
    }
}
