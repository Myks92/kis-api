<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Profile;

use Api\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Id;
use Myks92\User\Model\User\Entity\User\Name;
use Myks92\User\Model\User\Service\PasswordHasher;

class ProfileFixture extends Fixture
{
    public const USER_ID = '00000000-0000-0000-0000-100000000001';
    public const NEW_EMAIL = 'profile-new-email@app.test';

    private PasswordHasher $hasher;

    public function __construct(PasswordHasher $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function userCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => 'profile-user@app.test',
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public static function userNewEmailCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => self::NEW_EMAIL,
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->withId(new Id(self::USER_ID))
            ->withEmail(new Email('profile-user@app.test'))
            ->withHash($this->hasher->hash('password'))
            ->withName(new Name('Profile', 'User'))
            ->active()
            ->build();

        $user->attachNetwork('facebook', '1111');

        $manager->persist($user);

        $manager->flush();
    }
}
