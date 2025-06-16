<?php

namespace app\repositories\interfaces;

use app\models\AuthorSubscription;

interface AuthorSubscriptionRepositoryInterface
{
    public function findById(int $id): ?AuthorSubscription;
    public function findByAuthorId(int $authorId): array;
    public function findByAuthorAndPhone(int $authorId, string $phone): ?AuthorSubscription;
    public function save(AuthorSubscription $subscription): bool;
    public function delete(AuthorSubscription $subscription): bool;
}