<?php

declare(strict_types=1);

namespace Api\Tests\Functional\Profile;

use DateTimeImmutable;
use Myks92\User\Model\User\Entity\User\Token;
use Myks92\User\Model\User\Service\TokenizerInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class TestTokenizer implements TokenizerInterface
{
    public const TOKEN = '00000000-0000-0000-0000-000000000001';

    /**
     * @inheritDoc
     */
    public function generate(DateTimeImmutable $date): Token
    {
        return new Token(self::TOKEN, $date->modify('+1 day'));
    }
}
