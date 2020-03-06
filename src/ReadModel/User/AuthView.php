<?php

declare(strict_types=1);

namespace Api\ReadModel\User;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class AuthView
{
    public string $id;
    public string $email;
    public string $password_hash;
    public string $name;
    public string $role;
    public string $status;
}
