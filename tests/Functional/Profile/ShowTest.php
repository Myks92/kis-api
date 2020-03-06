<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Profile;

use Api\Tests\Functional\DbWebTestCase;

class ShowTest extends DbWebTestCase
{
    private const URL = '/profile';

    public function testGuest(): void
    {
        $this->client->request('GET', self::URL);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testUser(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());
        $this->client->request('GET', self::URL);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([
            'id' => ProfileFixture::USER_ID,
            'email' => 'profile-user@app.test',
            'first_name' => 'Profile',
            'last_name' => 'User',
            'networks' => [
                [
                    'name' => 'facebook',
                    'identity' => '1111',
                ]
            ],
        ],
        $data);
    }
}