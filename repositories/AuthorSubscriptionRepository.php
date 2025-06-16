<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\AuthorSubscription;
use app\repositories\interfaces\AuthorSubscriptionRepositoryInterface;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class AuthorSubscriptionRepository implements AuthorSubscriptionRepositoryInterface
{
    public function findById(int $id): ?AuthorSubscription
    {
        return AuthorSubscription::findOne($id);
    }

    public function findByAuthorId(int $authorId): array
    {
        return AuthorSubscription::find()
            ->where(['author_id' => $authorId])
            ->all();
    }

    public function findByAuthorAndPhone(int $authorId, string $phone): ?AuthorSubscription
    {
        return AuthorSubscription::findOne([
            'author_id' => $authorId,
            'phone' => $phone
        ]);
    }

    /**
     * @throws Exception
     */
    public function save(AuthorSubscription $subscription): bool
    {
        return $subscription->save();
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete(AuthorSubscription $subscription): bool
    {
        return $subscription->delete() !== false;
    }
}