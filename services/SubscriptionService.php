<?php

declare(strict_types=1);

namespace app\services;

use app\models\AuthorSubscription;
use app\models\Book;
use app\repositories\interfaces\AuthorSubscriptionRepositoryInterface;
use app\services\interfaces\SmsServiceInterface;
use app\services\interfaces\LoggerInterface;
use app\services\interfaces\SubscriptionServiceInterface;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function __construct(
        private readonly AuthorSubscriptionRepositoryInterface $subscriptionRepository,
        private readonly SmsServiceInterface $smsService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Создает подписку на автора
     */
    public function subscribe(int $authorId, string $phone, ?int $userId = null): bool
    {
        $subscription = new AuthorSubscription();
        $subscription->author_id = $authorId;
        $subscription->phone = $phone;
        $subscription->user_id = $userId;
        
        if (!$subscription->validate()) {
            $this->logger->error('Ошибка валидации данных подписки', [
                'authorId' => $authorId,
                'phone' => $phone,
                'errors' => $subscription->errors
            ]);
            return false;
        }

        if (!$this->subscriptionRepository->save($subscription)) {
            $this->logger->error('Ошибка при создании подписки', [
                'authorId' => $authorId,
                'phone' => $phone,
                'errors' => $subscription->errors
            ]);
            return false;
        }

        return true;
    }

    /**
     * Отправляет уведомления о новой книге
     */
    public function notifyAboutNewBook(Book $book): void
    {
        // Получаем всех авторов книги
        $authors = $book->authors;
        
        if (empty($authors)) {
            $this->logger->warning('Книга не имеет авторов для отправки уведомлений', [
                'bookId' => $book->id,
                'title' => $book->title
            ]);
            return;
        }

        // Для каждого автора книги отправляем уведомления подписчикам (лучше бы сделать через очередь)
        foreach ($authors as $author) {
            $subscriptions = $this->subscriptionRepository->findByAuthorId($author->id);

            foreach ($subscriptions as $subscription) {
                $message = sprintf(
                    'Новая книга "%s" от автора %s уже доступна в нашей библиотеке!',
                    $book->title,
                    $author->name
                );

                if (!$this->smsService->send($subscription->phone, $message)) {
                    $this->logger->error('Ошибка при отправке SMS', [
                        'bookId' => $book->id,
                        'authorId' => $author->id,
                        'phone' => $subscription->phone
                    ]);
                }
            }
        }
    }

    /**
     * Отписывает от автора
     */
    public function unsubscribe(int $authorId, string $phone): bool
    {
        $subscription = $this->subscriptionRepository->findByAuthorAndPhone($authorId, $phone);

        if (!$subscription) {
            return false;
        }

        if (!$this->subscriptionRepository->delete($subscription)) {
            $this->logger->error('Ошибка при удалении подписки', [
                'authorId' => $authorId,
                'phone' => $phone
            ]);
            return false;
        }

        return true;
    }
} 