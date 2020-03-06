<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Auth\Reset;

use Api\Tests\Functional\DbWebTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class RequestResetPasswordTest extends DbWebTestCase
{
    use ArraySubsetAsserts;

    private const URL = '/auth/reset-password';

    public function testGet(): void
    {
        $this->client->request('GET', self::URL);

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $this->client->setServerParameters(ResetFixture::userCredentials());
        $this->client->request('PUT', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'reset-user@app.test'])
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals([], $data);
    }

    public function testNotValid(): void
    {
        $this->client->request('PUT', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'not-email'])
        );
        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
    }
}