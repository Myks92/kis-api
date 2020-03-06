<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Users;

use Api\Tests\Functional\AuthFixture;
use Api\Tests\Functional\DbWebTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class CreateTest extends DbWebTestCase
{
    use ArraySubsetAsserts;

    const URL = '/users/create';

    public function testGuest(): void
    {
        $this->client->request('POST', self::URL);
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(AuthFixture::userCredentials());
        $this->client->request('POST', self::URL);

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCreate(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'first_name' => 'First',
                'last_name' => 'Last',
                'email' => 'create-user@app.test',
            ])
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testNotValid(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'first_name' => '',
                'last_name' => '',
                'email' => 'not-email',
            ])
        );
        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArraySubset([
            'violations' => [
                ['propertyPath' => 'email', 'title' => 'This value is not a valid email address.'],
                ['propertyPath' => 'first_name', 'title' => 'This value should not be blank.'],
                ['propertyPath' => 'last_name', 'title' => 'This value should not be blank.'],
            ],
        ], $data);
    }

    public function testExists(): void
    {
        $this->client->setServerParameters(AuthFixture::adminCredentials());
        $this->client->request('POST', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'first_name' => 'First',
                'last_name' => 'Last',
                'email' => 'exesting-user@app.test',
            ])
        );

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArraySubset([
            'error' => [
                'code' => 400,
                'message' => 'User with this email already exists.',
            ]
        ], $data);
    }
}
