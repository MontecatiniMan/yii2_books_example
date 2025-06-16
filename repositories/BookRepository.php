<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Book;
use app\repositories\interfaces\BookRepositoryInterface;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use Yii;

class BookRepository implements BookRepositoryInterface
{
    public function findById(int $id): ?Book
    {
        return Book::findOne($id);
    }

    public function findAll(): array
    {
        return Book::find()->with('authors')->all();
    }

    /**
     * @throws Exception
     */
    public function save(Book $book): bool
    {
        return $book->save();
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete(Book $book): bool
    {
        return $book->delete() !== false;
    }

    public function findByAuthorId(int $authorId): array
    {
        return Book::find()
            ->joinWith('authors')
            ->where(['authors.id' => $authorId])
            ->all();
    }

    public function getDataProvider(): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => Book::find()->with('authors'),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'title',
                    'publication_year',
                    'created_at',
                ],
            ],
        ]);
    }

    public function getAuthorIds(Book $book): array
    {
        return ArrayHelper::getColumn($book->authors, 'id');
    }

    /**
     * @throws Throwable
     */
    public function setAuthorIds(Book $book, array $authorIds): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Удаляем старые связи
            Yii::$app->db->createCommand()
                ->delete('{{%book_author}}', ['book_id' => $book->id])
                ->execute();

            // Добавляем новые связи
            if (!empty($authorIds)) {
                $rows = [];
                foreach ($authorIds as $authorId) {
                    $rows[] = [$book->id, $authorId];
                }
                Yii::$app->db->createCommand()
                    ->batchInsert('{{%book_author}}', ['book_id', 'author_id'], $rows)
                    ->execute();
            }

            $transaction->commit();
            return true;
        } catch (Throwable $th) {
            $transaction->rollBack();
            throw $th;
        }
    }

    /**
     * @throws Throwable
     */
    public function saveWithAuthors(Book $book, array $authorIds): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            if (!$this->save($book)) {
                $transaction->rollBack();
                return false;
            }

            if (!$this->setAuthorIds($book, $authorIds)) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (Throwable $th) {
            $transaction->rollBack();
            throw $th;
        }
    }

    public function getCoverImagePath(Book $book): ?string
    {
        return $book->cover_image;
    }

    /**
     * @throws Exception
     */
    public function updateCoverImage(Book $book, ?string $coverPath): bool
    {
        $book->cover_image = $coverPath;
        return $book->save(false, ['cover_image', 'updated_at']);
    }
} 