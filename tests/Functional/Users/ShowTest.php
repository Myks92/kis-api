<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;
use DateTimeImmutable;
use Myks92\User\Model\User\Entity\User\Id;

class ShowTest extends DbWebTestCase
{
    private const URL = '/users/%s';

    public function testGuest(): void
    {
        $this->client->request('GET', sprintf(self::URL, UsersFixture::EXISTING_ID));
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('GET', sprintf(self::URL, UsersFixture::EXISTING_ID));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('GET', '/users/' . UsersFixture::EXISTING_ID);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'id' => UsersFixture::EXISTING_ID,
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'exesting-user@app.test',
            'role' => 'ROLE_USER',
            'status' => 'active',
            'date' => (new DateTimeImmutable('2020-01-01 00:00:00'))->format(DATE_ATOM)
        ], $data);
    }

    public function testNotFound(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('GET', '/users/' . Id::generate()->getValue());

        self::assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
