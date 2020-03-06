<?php

declare(strict_types=1);

namespace Api\Event\Dispatcher\Message;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class Message
{
    /**
     * @var object
     */
    private object $event;

    /**
     * @param object $event
     */
    public function __construct(object $event)
    {
        $this->event = $event;
    }

    /**
     * @return object
     */
    public function getEvent(): object
    {
        return $this->event;
    }
}
