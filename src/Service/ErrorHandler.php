<?php

declare(strict_types=1);

namespace Api\Service;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ErrorHandler
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Exception $e
     */
    public function handle(Exception $e): void
    {
        $this->logger->warning($e->getMessage(), ['exception' => $e]);
    }
}
