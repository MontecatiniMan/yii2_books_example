<?php

declare(strict_types=1);

namespace app\services;

use app\services\interfaces\LoggerInterface;
use Yii;

class Logger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        $this->logMessage('emergency', $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->logMessage('alert', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->logMessage('critical', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->logMessage('error', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->logMessage('warning', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->logMessage('notice', $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->logMessage('info', $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->logMessage('debug', $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logMessage($level, $message, $context);
    }

    private function logMessage($level, $message, array $context = []): void
    {
        $formattedMessage = $message;
        if (!empty($context)) {
            $formattedMessage .= ' Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        switch ($level) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                Yii::error($formattedMessage, 'app');
                break;
            case 'warning':
                Yii::warning($formattedMessage, 'app');
                break;
            case 'notice':
            case 'info':
                Yii::info($formattedMessage, 'app');
                break;
            case 'debug':
                Yii::debug($formattedMessage, 'app');
                break;
            default:
                Yii::info($formattedMessage, 'app');
        }
    }
} 