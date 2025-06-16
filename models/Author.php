<?php

namespace app\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Модель автора
 * 
 * @property int $id ID автора
 * @property string $name Имя автора
 * @property int $created_at Дата создания (timestamp)
 * @property int $updated_at Дата обновления (timestamp)
 * @property int|null $book_count Виртуальное поле для количества книг (используется в отчетах)
 * 
 * @property-read Book[] $books Книги автора
 */
class Author extends ActiveRecord
{
    /**
     * Виртуальное поле для количества книг (используется в отчетах)
     */
    public $book_count;

    public static function tableName(): string
    {
        return '{{%authors}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['book_count'], 'integer', 'min' => 0],
            [['book_count'], 'safe'], // Разрешаем массовое присвоение для отчетов
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function getBooks(): ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id']);
    }
} 