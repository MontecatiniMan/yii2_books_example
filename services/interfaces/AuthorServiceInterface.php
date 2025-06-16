<?php

namespace app\services\interfaces;

use app\models\Author;
use yii\data\ActiveDataProvider;

interface AuthorServiceInterface
{
    public function getDataProvider(): ActiveDataProvider;
    public function getAuthor(int $id): Author;
    public function getAllAuthors(): array;
    public function createAuthor(Author $author): Author;
    public function updateAuthor(int $id, Author $authorData): Author;
    public function deleteAuthor(int $id): void;
    public function getTopAuthorsByYear(int $year, int $limit = 10): array;
    public function getAuthorBooks(int $authorId): array;
} 