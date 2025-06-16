<?php

namespace app\services\interfaces;

use app\models\Book;
use yii\data\ActiveDataProvider;

interface BookServiceInterface
{
    public function getDataProvider(): ActiveDataProvider;
    public function getBook(int $id): Book;
    public function getAllBooks(): array;
    public function updateBook(int $id, Book $bookData): Book;
    public function deleteBook(int $id): void;
    public function getBooksByAuthor(int $authorId): array;
    public function createBookWithAuthors(Book $book, array $authorIds): Book;
    public function updateBookWithAuthors(int $id, Book $bookData, array $authorIds): Book;
    public function getBookAuthorIds(int $bookId): array;
    public function getBookCoverUrl(Book $book): string;
    public function updateBookCover(Book $book, ?string $coverPath): bool;
} 