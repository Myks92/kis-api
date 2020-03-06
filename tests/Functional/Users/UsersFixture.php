<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Builder\User\UserBuilder;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Id;
use Myks92\User\Model\User\Entity\User\Name;

class UsersFixture extends Fixture
{
    public const EXISTING_ID = '00000000-0000-0000-0000-000000000001';
    public const ACTIVE_ID = '00000000-0000-0000-0000-000000000002';
    public const WAIT_ID = '00000000-0000-0000-0000-000000000003';
    public const BLOCK_ID = '00000000-0000-0000-0000-000000000004';

    public function load(ObjectManager $manager): void
    {
        $existing = (new UserBuilder())
            ->withId(new Id(self::EXISTING_ID))
            ->withEmail(new Email('exesting-user@app.test'))
            ->withDate(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->active()
            ->build();

        $manager->persist($existing);

        $show = (new UserBuilder())
            ->withEmail(new Email('show-user@app.test'))
            ->withName(new Name('Show', 'User'))
            ->active()
            ->build();

        $manager->persist($show);

        $active = (new UserBuilder())
            ->withId(new Id(self::ACTIVE_ID))
            ->withEmail(new Email('active-user@app.test'))
            ->withName(new Name('Active', 'User'))
            ->active()
            ->build();

        $manager->persist($active);

        $wait = (new UserBuilder())
            ->withId(new Id(self::WAIT_ID))
            ->withEmail(new Email('wait-user@app.test'))
            ->withName(new Name('Wait', 'User'))
            ->build();

        $manager->persist($wait);

        $block = (new UserBuilder())
            ->withId(new Id(self::BLOCK_ID))
            ->withEmail(new Email('block-user@app.test'))
            ->withName(new Name('Block', 'User'))
            ->active()
            ->build();

        $block->block();

        $manager->persist($block);

        $manager->flush();
    }
}
