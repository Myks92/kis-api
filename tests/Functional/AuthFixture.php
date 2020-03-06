<?php

declare(strict_types=1);

namespace Api\Tests\Functional;

use Api\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Id;
use Myks92\User\Model\User\Entity\User\Role;
use Myks92\User\Model\User\Service\PasswordHasher;

class AuthFixture extends Fixture
{
    public const REFERENCE_ADMIN = 'test_auth_admin';
    public const REFERENCE_USER = 'test_auth_user';

    public const ADMIN_ID = '00000000-0000-0000-0000-200000000001';
    public const USER_ID = '00000000-0000-0000-0000-200000000002';

    private PasswordHasher $hasher;

    public function __construct(PasswordHasher $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function userCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => 'auth-user@app.test',
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public static function adminCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => 'auth-admin@app.test',
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $hash = $this->hasher->hash('password');

        $user = (new UserBuilder())
            ->withId(new Id(self::USER_ID))
            ->withEmail(new Email('auth-user@app.test'))
            ->withHash($hash)
            ->active()
            ->build();

        $manager->persist($user);
        $this->setReference(self::REFERENCE_USER, $user);

        $admin = (new UserBuilder())
            ->withId(new Id(self::ADMIN_ID))
            ->withEmail(new Email('auth-admin@app.test'))
            ->withHash($hash)
            ->withRole(Role::admin())
            ->active()
            ->build();

        $manager->persist($admin);
        $this->setReference(self::REFERENCE_ADMIN, $admin);

        $manager->flush();
    }
}
