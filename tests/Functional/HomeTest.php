<?php

declare(strict_types=1);

namespace Api\Tests\Functional;

class HomeTest extends DbWebTestCase
{
    public function testGet(): void
    {
        $this->client->request('GET', '/');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());

        $data = json_decode($content, true);

        self::assertEquals(
            [
                'name' => 'API',
                'version' => '1.0',
            ],
            $data
        );
    }

    public function testPost(): void
    {
        $this->client->request('POST', '/');

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }
}
