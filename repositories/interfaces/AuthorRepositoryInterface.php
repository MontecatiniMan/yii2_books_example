<?php

namespace app\repositories\interfaces;

use app\models\Author;
use yii\data\ActiveDataProvider;

interface AuthorRepositoryInterface
{
    public function findById(int $id): ?Author;
    public function findAll(): array;
    public function save(Author $author): bool;
    public function delete(Author $author): bool;
    public function findTopAuthorsByYear(int $year, int $limit = 10): array;
    public function getDataProvider(): ActiveDataProvider;
}