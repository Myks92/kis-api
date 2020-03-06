<?php

declare(strict_types=1);

namespace Api\Event\Dispatcher;

use Api\Event\Dispatcher\Message\Message;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class MessengerEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;

    /**
     * @param MessageBusInterface $bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @param object $event
     */
    public function dispatch(object $event): void
    {
        $this->bus->dispatch(new Message($event));
    }
}
