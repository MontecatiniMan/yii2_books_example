<?php

namespace app\repositories\interfaces;

use app\models\Book;
use yii\data\ActiveDataProvider;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;
    public function findAll(): array;
    public function save(Book $book): bool;
    public function delete(Book $book): bool;
    public function findByAuthorId(int $authorId): array;
    public function getDataProvider(): ActiveDataProvider;
    public function getAuthorIds(Book $book): array;
    public function setAuthorIds(Book $book, array $authorIds): bool;
    public function saveWithAuthors(Book $book, array $authorIds): bool;
    public function getCoverImagePath(Book $book): ?string;
    public function updateCoverImage(Book $book, ?string $coverPath): bool;
} 