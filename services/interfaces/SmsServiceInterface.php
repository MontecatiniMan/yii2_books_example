<?php

namespace app\services\interfaces;

interface SmsServiceInterface
{
    /**
     * Отправляет SMS сообщение
     * @param string $phone Номер телефона
     * @param string $message Текст сообщения
     * @return bool Результат отправки
     */
    public function send(string $phone, string $message): bool;
} 