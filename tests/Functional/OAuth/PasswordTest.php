<?php

declare(strict_types=1);

namespace Api\Tests\Functional\OAuth;

use Api\Tests\Functional\DbWebTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class PasswordTest extends DbWebTestCase
{
    use ArraySubsetAsserts;

    private const URL = '/token';

    public function testMethod(): void
    {
        $this->client->request('GET', self::URL);
        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $this->client->request('POST', self::URL, [
            'grant_type' => 'password',
            'username' => 'oauth-password-user@app.test',
            'password' => 'password',
            'client_id' => 'oauth',
            'client_secret' => 'secret',
            'access_type' => 'offline',
        ]);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertArraySubset(['token_type' => 'Bearer'], $data);

        self::assertArrayHasKey('expires_in', $data);
        self::assertNotEmpty($data['expires_in']);

        self::assertArrayHasKey('access_token', $data);
        self::assertNotEmpty($data['access_token']);

        self::assertArrayHasKey('refresh_token', $data);
        self::assertNotEmpty($data['refresh_token']);
    }

    public function testInvalid(): void
    {
        $this->client->request('POST', self::URL, [
            'grant_type' => 'password',
            'username' => 'oauth-password-user@app.test',
            'password' => 'invalid',
            'client_id' => 'oauth',
            'client_secret' => 'secret',
        ]);

        self::assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
