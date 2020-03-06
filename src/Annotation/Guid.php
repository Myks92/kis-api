<?php

declare(strict_types=1);

namespace Api\Annotation;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class Guid
{
    public const PATTERN = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
}
