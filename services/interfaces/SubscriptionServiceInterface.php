<?php

namespace app\services\interfaces;

use app\models\Book;

interface SubscriptionServiceInterface
{
    /**
     * Создает подписку на автора
     */
    public function subscribe(int $authorId, string $phone, ?int $userId = null): bool;

    /**
     * Отправляет уведомления о новой книге
     */
    public function notifyAboutNewBook(Book $book): void;

    /**
     * Отписывает от автора
     */
    public function unsubscribe(int $authorId, string $phone): bool;
} 