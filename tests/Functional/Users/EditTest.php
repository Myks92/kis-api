<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;

class EditTest extends DbWebTestCase
{
    private const URL = '/users/%s';

    public function testGuest(): void
    {
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::ACTIVE_ID));
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
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
        $this->client->request('PUT', sprintf(self::URL, UsersFixture::WAIT_ID), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'first_name' => 'Edit First',
                'last_name' => 'Edit Last',
                'email' => 'edit-user@app.test',
            ])
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);
    }
}
