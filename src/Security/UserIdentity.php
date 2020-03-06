<?php

declare(strict_types=1);

namespace Api\Security;

use Myks92\User\Model\User\Entity\User\Status;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class UserIdentity implements UserInterface, EquatableInterface
{
    /**
     * @var string
     */
    private string $id;
    /**
     * @var string
     */
    private string $username;
    /**
     * @var string
     */
    private string $password;
    /**
     * @var string
     */
    private string $display;
    /**
     * @var string
     */
    private string $role;
    /**
     * @var string
     */
    private string $status;

    /**
     * @param string $id
     * @param string $username
     * @param string $password
     * @param string $display
     * @param string $role
     * @param string $status
     */
    public function __construct(
        string $id,
        string $username,
        string $password,
        string $display,
        string $role,
        string $status
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->display = $display;
        $this->role = $role;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === Status::active()->getName();
    }

    /**
     * @return string
     */
    public function getDisplay(): string
    {
        return $this->display;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     *
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return
            $this->id === $user->id &&
            $this->password === $user->password &&
            $this->role === $user->role &&
            $this->status === $user->status;
    }
}
