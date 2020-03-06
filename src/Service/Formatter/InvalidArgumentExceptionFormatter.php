<?php

declare(strict_types=1);

namespace Api\Service\Formatter;

use Api\Service\ErrorHandler;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class InvalidArgumentExceptionFormatter implements EventSubscriberInterface
{
    /**
     * @var ErrorHandler
     */
    private ErrorHandler $errors;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param ErrorHandler $errors
     * @param TranslatorInterface $translator
     */
    public function __construct(ErrorHandler $errors, TranslatorInterface $translator)
    {
        $this->errors = $errors;
        $this->translator = $translator;
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

        if (!$exception instanceof InvalidArgumentException) {
            return;
        }

        $this->errors->handle($exception);

        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => 422,
                'message' => $this->translator->trans($exception->getMessage(), [], 'error-access')
            ]
        ], 422));
    }
}
