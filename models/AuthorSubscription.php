<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Модель подписки на автора
 * 
 * @property int $id ID подписки
 * @property int $author_id ID автора
 * @property string $phone Номер телефона подписчика
 * @property int|null $user_id ID пользователя (если авторизован)
 * @property int $created_at Дата создания подписки (timestamp)
 * 
 * @property-read Author $author Автор
 * @property-read User|null $user Пользователь (если подписка создана авторизованным пользователем)
 */
class AuthorSubscription extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%author_subscriptions}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['author_id', 'phone'], 'required'],
            [['author_id', 'user_id', 'created_at'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'match', 'pattern' => '/^\+?[0-9]{10,15}$/'],
            [['author_id', 'phone'], 'unique', 'targetAttribute' => ['author_id', 'phone']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['author_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
} 