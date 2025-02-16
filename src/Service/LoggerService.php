<?php

namespace ImageResizer\Service;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class LoggerService
{
    private static ?LoggerService $instance = null;
    private MonologLogger $logger;

    private function __construct()
    {
        $this->logger = new MonologLogger('app');
        $logDir = getenv('LOG_FILE');
        $this->logger->pushHandler(new StreamHandler($logDir));
    }

    public static function getInstance(): LoggerService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }
}
