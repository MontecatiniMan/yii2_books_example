<?php

namespace app\models;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Модель книги
 * 
 * @property int $id ID книги
 * @property string $title Название книги
 * @property string|null $description Описание книги
 * @property int $publication_year Год издания
 * @property string|null $isbn ISBN книги
 * @property string|null $cover_image Обложка книги
 * @property int $created_at Дата создания (timestamp)
 * @property int $updated_at Дата обновления (timestamp)
 * 
 * @property-read Author[] $authors Авторы книги
 */
class Book extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%books}}';
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
            [['title', 'publication_year'], 'required'],
            [['description'], 'string'],
            [['publication_year'], 'integer', 'min' => 1000, 'max' => date('Y')],
            [['isbn'], 'string', 'max' => 13],
            [['isbn'], 'unique', 'when' => function($model) {
                return !empty($model->isbn);
            }, 'filter' => function($query) {
                if (!$this->isNewRecord) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }
                return $query;
            }],
            [['title', 'cover_image'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'Название',
            'description' => 'Описание',
            'publication_year' => 'Год издания',
            'isbn' => 'ISBN',
            'cover_image' => 'Обложка',
            'authors' => 'Авторы',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function getAuthors(): ActiveQuery
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id']);
    }
}