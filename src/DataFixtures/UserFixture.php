<?php

namespace Api\DataFixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Id;
use Myks92\User\Model\User\Entity\User\Name;
use Myks92\User\Model\User\Entity\User\Role;
use Myks92\User\Model\User\Entity\User\Token;
use Myks92\User\Model\User\Entity\User\User;
use Myks92\User\Model\User\Service\PasswordHasher;
use Ramsey\Uuid\Uuid;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class UserFixture extends Fixture
{
    public const REFERENCE_ADMIN = 'user_user_admin';
    public const REFERENCE_USER = 'user_user_user';

    /**
     * @var PasswordHasher
     */
    private PasswordHasher $hasher;

    /**
     * @param PasswordHasher $hasher
     */
    public function __construct(PasswordHasher $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $hash = $this->hasher->hash('password');

        $network = $this->createSignedUpByNetwork(
            new Name('David', 'Black'),
            new Email('network@app.test'),
            'facebook',
            '1000000'
        );
        $manager->persist($network);

        $requested = $this->createSignUpRequestedByEmail(
            new Name('John', 'Doe'),
            new Email('requested@app.test'),
            $hash
        );
        $manager->persist($requested);

        $confirmed = $this->createSignUpConfirmedByEmail(
            new Name('Brad', 'Pitt'),
            new Email('user@app.test'),
            $hash
        );
        $manager->persist($confirmed);
        $this->setReference(self::REFERENCE_USER, $confirmed);

        $admin = $this->createAdminByEmail(
            new Name('James', 'Bond'),
            new Email('admin@app.test'),
            $hash
        );
        $manager->persist($admin);
        $this->setReference(self::REFERENCE_ADMIN, $admin);

        $manager->flush();
    }

    /**
     * @param Name $name
     * @param Email $email
     * @param string $network
     * @param string $identity
     *
     * @return User
     * @throws Exception
     */
    private function createSignedUpByNetwork(Name $name, Email $email, string $network, string $identity): User
    {
        return User::joinByNetwork(
            Id::generate(),
            new DateTimeImmutable(),
            $name,
            $email,
            $network,
            $identity
        );
    }

    /**
     * @param Name $name
     * @param Email $email
     * @param string $hash
     *
     * @return User
     * @throws Exception
     */
    private function createSignUpRequestedByEmail(Name $name, Email $email, string $hash): User
    {
        return User::requestJoinByEmail(
            Id::generate(),
            new DateTimeImmutable(),
            $name,
            $email,
            $hash,
            new Token(Uuid::uuid4()->toString(), new DateTimeImmutable())
        );
    }

    /**
     * @param Name $name
     * @param Email $email
     * @param string $hash
     *
     * @return User
     * @throws Exception
     */
    private function createSignUpConfirmedByEmail(Name $name, Email $email, string $hash): User
    {
        $user = $this->createSignUpRequestedByEmail($name, $email, $hash);
        $user->getJoinConfirmToken();
        return $user;
    }

    /**
     * @param Name $name
     * @param Email $email
     * @param string $hash
     *
     * @return User
     * @throws Exception
     */
    private function createAdminByEmail(Name $name, Email $email, string $hash): User
    {
        $user = $this->createSignUpConfirmedByEmail($name, $email, $hash);
        $user->changeRole(Role::admin());
        return $user;
    }
}
