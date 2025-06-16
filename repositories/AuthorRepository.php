<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Author;
use app\repositories\interfaces\AuthorRepositoryInterface;
use Throwable;
use yii\db\Exception;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;

class AuthorRepository implements AuthorRepositoryInterface
{
    public function findById(int $id): ?Author
    {
        return Author::findOne($id);
    }

    public function findAll(): array
    {
        return Author::find()->all();
    }

    /**
     * @throws Exception
     */
    public function save(Author $author): bool
    {
        return $author->save();
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete(Author $author): bool
    {
        return $author->delete() !== false;
    }

    public function findTopAuthorsByYear(int $year, int $limit = 10): array
    {
        try {
            $hasData = Author::find()->exists();

            if (!$hasData) {
                return [];
            }
            
            return Author::find()
                ->select([
                    'authors.*', 
                    'book_count' => new Expression('COUNT(DISTINCT books.id)')
                ])
                ->joinWith('books')
                ->where(['books.publication_year' => $year])
                ->groupBy(['authors.id'])
                ->orderBy(['book_count' => SORT_DESC])
                ->limit($limit)
                ->all();
        } catch (Throwable $th) {
            return [];
        }
    }

    public function getDataProvider(): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => Author::find()->with('books'),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'name',
                    'created_at',
                ],
            ],
        ]);
    }
}