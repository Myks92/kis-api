<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class RemoveTest extends DbWebTestCase
{
    use ArraySubsetAsserts;

    private const URL = '/users/%s';

    public function testGuest(): void
    {
        $this->client->request('DELETE', sprintf(self::URL, UsersFixture::WAIT_ID));
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('DELETE', sprintf(self::URL, UsersFixture::WAIT_ID), [], [],
            ['CONTENT_TYPE' => 'application/json']
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('DELETE', sprintf(self::URL, UsersFixture::WAIT_ID));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);
    }

    public function testSelf(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('DELETE', sprintf(self::URL, AuthFixture::ADMIN_ID));

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
    }

    public function testNotWait(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('DELETE', sprintf(self::URL, UsersFixture::ACTIVE_ID));

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'error' => [
                'code' => 400,
                'message' => 'Unable to remove active user.',
            ]
        ], $data);
    }
}
