<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Profile;

use Api\Tests\Functional\DbWebTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Myks92\User\Model\User\Entity\User\Id;

class ChangeNameTest extends DbWebTestCase
{
    use ArraySubsetAsserts;

    private const URL = '/profile/change-name';

    public function testGuest(): void
    {
        $this->client->request('PUT', self::URL);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testGet(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());
        $this->client->request('GET', self::URL);

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testPost(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());
        $this->client->request('POST', self::URL);

        self::assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    public function testPut(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());

        $this->client->request('PUT', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'id' => Id::generate(), // fake id
                'first' => 'Tom',
                'last' => 'Bent',
            ])
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertEquals([], $data);

        $this->client->request('GET', '/profile');
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'first_name' => 'Tom',
            'last_name' => 'Bent',
        ], $data);
    }

    public function testNotValid(): void
    {
        $this->client->setServerParameters(ProfileFixture::userCredentials());

        $this->client->request('PUT', self::URL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
        self::assertJson($content = $this->client->getResponse()->getContent());
        $data = json_decode($content, true);

        self::assertArraySubset([
            'violations' => [
                ['propertyPath' => 'first', 'title' => 'This value should not be blank.'],
                ['propertyPath' => 'last', 'title' => 'This value should not be blank.'],
            ],
        ], $data);
    }
}