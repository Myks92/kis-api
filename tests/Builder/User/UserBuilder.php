<?php
declare(strict_types=1);


namespace Api\Tests\Builder\User;


use DateTimeImmutable;
use Exception;
use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Id;
use Myks92\User\Model\User\Entity\User\Name;
use Myks92\User\Model\User\Entity\User\Role;
use Myks92\User\Model\User\Entity\User\Token;
use Myks92\User\Model\User\Entity\User\User;

class UserBuilder
{
    private Id $id;
    private DateTimeImmutable $date;
    private Name $name;
    private Email $email;
    private string $hash;
    private Token $joinConfirmToken;
    private bool $active = false;

    private ?string $network = null;
    private ?string $identity = null;

    private ?Role $role = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->id = Id::generate();
        $this->email = new Email('mail@app.test');
        $this->name = new Name('First', 'Last');
        $this->hash = 'hash';
        $this->date = new DateTimeImmutable();
        $this->joinConfirmToken = new Token('00000000-0000-0000-0000-000000000001', $this->date->modify('+1 day'));
    }

    public function withEmail(Email $email): self
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function active(): self
    {
        $clone = clone $this;
        $clone->active = true;
        return $clone;
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withName(Name $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withHash(string $hash): self
    {
        $clone = clone $this;
        $clone->hash = $hash;
        return $clone;
    }

    public function withDate(DateTimeImmutable $date): self
    {
        $clone = clone $this;
        $clone->date = $date;
        return $clone;
    }

    public function withRole(Role $role): self
    {
        $clone = clone $this;
        $clone->role = $role;
        return $clone;
    }

    /**
     * @return User
     * @throws Exception
     */
    public function build(): User
    {
        if ($this->network) {
            return User::joinByNetwork(
                $this->id,
                $this->date,
                $this->name,
                $this->email,
                $this->network,
                $this->identity
            );
        }

        $user = User::requestJoinByEmail(
            $this->id,
            $this->date,
            $this->name,
            $this->email,
            $this->hash,
            $this->joinConfirmToken
        );

        if ($this->active) {
            $user->confirmJoin(
                $this->joinConfirmToken->getValue(),
                $this->joinConfirmToken->getExpires()->modify('-1 day')
            );
        }

        if ($this->role) {
            $user->changeRole($this->role);
        }

        return $user;
    }
}