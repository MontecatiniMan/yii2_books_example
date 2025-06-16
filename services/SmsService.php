<?php

declare(strict_types=1);

namespace app\services;

use app\services\interfaces\SmsServiceInterface;
use app\services\interfaces\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Throwable;
use Yii;

class SmsService implements SmsServiceInterface
{
    private string $apiKey;
    private string $apiUrl = 'https://smspilot.ru/api.php';
    private Client $httpClient;

    public function __construct(private readonly LoggerInterface $logger) {
        $this->apiKey = Yii::$app->params['smspilotApiKey'] ?? '';
        $this->httpClient = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
        ]);
    }

    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            $this->logger->error('SMS API key is not configured');
            return false;
        }

        try {
            $response = $this->httpClient->post($this->apiUrl, [
                'form_params' => [
                    'send' => $message,
                    'to' => $phone,
                    'apikey' => $this->apiKey,
                    'format' => 'json',
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (isset($result['success']) && $result['success'] === true) {
                $this->logger->info('SMS sent successfully', [
                    'phone' => $phone,
                    'message_length' => strlen($message)
                ]);
                return true;
            } else {
                $this->logger->warning('SMS sending failed - API returned error', [
                    'phone' => $phone,
                    'response' => $result
                ]);
                return false;
            }

        } catch (RequestException $e) {
            $this->logger->error('SMS sending failed - HTTP request error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return false;
        } catch (Throwable $th) {
            $this->logger->error('SMS sending error - unexpected exception', [
                'phone' => $phone,
                'error' => $th->getMessage(),
                'exception_class' => get_class($th)
            ]);
            return false;
        }
    }
} 