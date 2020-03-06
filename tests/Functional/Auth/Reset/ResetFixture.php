<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Auth\Reset;

use Api\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Id;
use Myks92\User\Model\User\Entity\User\Name;
use Myks92\User\Model\User\Service\PasswordHasher;

class ResetFixture extends Fixture
{
    public const USER_ID = '00000000-0000-0000-0000-100000000005';

    private PasswordHasher $hasher;

    public function __construct(PasswordHasher $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function userCredentials(): array
    {
        return [
            'PHP_AUTH_USER' => 'reset-user@app.test',
            'PHP_AUTH_PW' => 'password',
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->withId(new Id(self::USER_ID))
            ->withName(new Name('Reset', 'User'))
            ->withEmail(new Email('reset-user@app.test'))
            ->withHash($this->hasher->hash('password'))
            ->active()
            ->build();

        $user->attachNetwork('facebook', '5555');

        $manager->persist($user);

        $manager->flush();
    }
}
