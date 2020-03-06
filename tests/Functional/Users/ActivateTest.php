<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class ActivateTest extends DbWebTestCase
{
    use ArraySubsetAsserts;

    private const URL = '/users/%s/activate';

    public function testGuest(): void
    {
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::WAIT_ID));
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testGet(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('GET', sprintf(self::URL, UsersFixture::WAIT_ID));

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::WAIT_ID), [], [],
            ['CONTENT_TYPE' => 'application/json']
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::WAIT_ID));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);
    }

    public function testAlready(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::ACTIVE_ID));

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'error' => [
                'code' => 400,
                'message' => 'User is already active.',
            ]
        ], $data);
    }
}
