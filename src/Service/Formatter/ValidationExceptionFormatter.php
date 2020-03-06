<?php

declare(strict_types=1);

namespace Api\Service\Formatter;

use Api\Exception\ValidationException;
use Api\Service\ErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ValidationExceptionFormatter implements EventSubscriberInterface
{
    /**
     * @var ErrorHandler
     */
    private ErrorHandler $errors;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param ErrorHandler $errors
     * @param SerializerInterface $serializer
     */
    public function __construct(ErrorHandler $errors, SerializerInterface $serializer)
    {
        $this->errors = $errors;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ValidationException) {
            return;
        }

        $this->errors->handle($exception);

        $json = $this->serializer->serialize($exception->getViolations(), 'json');

        $event->setResponse(new JsonResponse($json, Response::HTTP_UNPROCESSABLE_ENTITY, [], true));
    }
}
