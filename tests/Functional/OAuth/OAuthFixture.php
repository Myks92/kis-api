<?php

declare(strict_types=1);

namespace Api\Tests\Functional\OAuth;

use Api\Tests\Builder\User\UserBuilder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Name;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;

class OAuthFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->withEmail(new Email('oauth-password-user@app.test'))
            ->withHash('$2y$12$qwnND33o8DGWvFoepotSju7eTAQ6gzLD/zy6W8NCVtiHPbkybz.w6') // 'password'
            ->withName(new Name('OAuth', 'User'))
            ->active()
            ->build();

        $manager->persist($user);

        $client = new Client('oauth', 'secret');
        $client->setGrants(new Grant(OAuth2Grants::PASSWORD));

        $manager->persist($client);

        $manager->flush();
    }
}
