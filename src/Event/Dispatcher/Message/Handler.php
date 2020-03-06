<?php

declare(strict_types=1);

namespace Api\Event\Dispatcher\Message;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class Handler implements MessageHandlerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Message $message
     */
    public function __invoke(Message $message)
    {
        $this->dispatcher->dispatch($message->getEvent());
    }
}
