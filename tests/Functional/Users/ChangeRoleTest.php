<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;
use Myks92\User\Model\User\Entity\User\Role;

class ChangeRoleTest extends DbWebTestCase
{
    const URL = '/users/%s/change-role';

    public function testGuest(): void
    {
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::ACTIVE_ID));
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testGet(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('GET', sprintf(self::URL, UsersFixture::ACTIVE_ID));

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::ACTIVE_ID));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSelf(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('PUT', sprintf(self::URL, AuthFixture::ADMIN_ID));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::ACTIVE_ID), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'role' => Role::ADMIN,
            ])
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);
    }
}
