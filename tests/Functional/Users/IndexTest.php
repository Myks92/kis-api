<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;

class IndexTest extends DbWebTestCase
{
    private const URL = '/users';

    public function testGuest(): void
    {
        $this->client->request('GET', self::URL);
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testPost(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('POST', self::URL);

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('GET', self::URL);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdmin(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $crawler = $this->client->request('GET', self::URL);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
