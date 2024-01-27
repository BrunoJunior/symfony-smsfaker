<?php

namespace BrunoJunior\Symfony\SmsFaker\Tests;

use Psr\Log\AbstractLogger;

final class TestLogger extends AbstractLogger
{
    public array $logs = [];

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}