<?php

declare(strict_types=1);

namespace Api\Container\User\Service;

use DateInterval;
use Exception;
use Myks92\User\Model\User\Service\Tokenizer;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ResetTokenizerFactory
{
    /**
     * @param string $interval
     *
     * @return Tokenizer
     * @throws Exception
     */
    public static function create(string $interval): Tokenizer
    {
        return new Tokenizer(new DateInterval($interval));
    }
}