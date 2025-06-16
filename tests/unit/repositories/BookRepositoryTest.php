<?php

namespace tests\unit\repositories;

use app\models\Book;
use app\models\Author;
use app\repositories\BookRepository;
use Codeception\Test\Unit;

class BookRepositoryTest extends Unit
{
    private BookRepository $repository;

    protected function _before()
    {
        $this->repository = new BookRepository();
    }

    public function testGetCoverImagePath()
    {
        // Создаем книгу с обложкой
        $book = new Book();
        $book->title = 'Тест обложки';
        $book->publication_year = 2024;
        $book->cover_image = 'uploads/covers/test.jpg';
        $this->assertTrue($book->save());

        // Проверяем получение пути к обложке
        $coverPath = $this->repository->getCoverImagePath($book);
        $this->assertEquals('uploads/covers/test.jpg', $coverPath);

        // Проверяем с пустой обложкой
        $book->cover_image = null;
        $book->save();
        $coverPath = $this->repository->getCoverImagePath($book);
        $this->assertNull($coverPath);
    }

    public function testUpdateCoverImage()
    {
        // Создаем книгу без обложки
        $book = new Book();
        $book->title = 'Тест обновления обложки';
        $book->publication_year = 2024;
        $this->assertTrue($book->save());
        $this->assertNull($book->cover_image);

        // Обновляем обложку
        $newCoverPath = 'uploads/covers/new-cover.jpg';
        $result = $this->repository->updateCoverImage($book, $newCoverPath);
        $this->assertTrue($result);

        // Перезагружаем из БД и проверяем
        $book->refresh();
        $this->assertEquals($newCoverPath, $book->cover_image);

        // Обновляем на null
        $result = $this->repository->updateCoverImage($book, null);
        $this->assertTrue($result);

        $book->refresh();
        $this->assertNull($book->cover_image);
    }

    public function testSaveWithAuthorsPreservesCoverImage()
    {
        // Создаем автора
        $author = new Author();
        $author->name = 'Тестовый автор';
        $this->assertTrue($author->save());

        // Создаем книгу с обложкой
        $book = new Book();
        $book->title = 'Книга с обложкой';
        $book->publication_year = 2024;
        $book->cover_image = 'uploads/covers/test-cover.jpg';

        // Сохраняем с авторами
        $result = $this->repository->saveWithAuthors($book, [$author->id]);
        $this->assertTrue($result);
        $this->assertNotNull($book->id);

        // Проверяем, что обложка сохранилась
        $savedBook = $this->repository->findById($book->id);
        $this->assertEquals('uploads/covers/test-cover.jpg', $savedBook->cover_image);
    }

    public function testFindByIdWithCoverImage()
    {
        // Создаем книгу с обложкой
        $book = new Book();
        $book->title = 'Поиск с обложкой';
        $book->publication_year = 2024;
        $book->cover_image = 'uploads/covers/find-test.jpg';
        $this->assertTrue($book->save());

        // Находим по ID
        $foundBook = $this->repository->findById($book->id);
        $this->assertNotNull($foundBook);
        $this->assertEquals('uploads/covers/find-test.jpg', $foundBook->cover_image);
    }

    public function testFindAllIncludesCoverImages()
    {
        // Создаем несколько книг с разными обложками
        $books = [];
        for ($i = 1; $i <= 3; $i++) {
            $book = new Book();
            $book->title = "Книга $i";
            $book->publication_year = 2024;
            $book->cover_image = $i % 2 === 0 ? "uploads/covers/book$i.jpg" : null;
            $this->assertTrue($book->save());
            $books[] = $book;
        }

        // Получаем все книги
        $allBooks = $this->repository->findAll();
        $this->assertGreaterThanOrEqual(3, count($allBooks));

        // Проверяем, что обложки загружены корректно
        $testBooks = array_filter($allBooks, function($book) {
            return strpos($book->title, 'Книга ') === 0;
        });

        $this->assertCount(3, $testBooks);
        
        foreach ($testBooks as $book) {
            if (strpos($book->title, '2') !== false) {
                $this->assertStringContainsString('book2.jpg', $book->cover_image);
            } elseif (strpos($book->title, '1') !== false || strpos($book->title, '3') !== false) {
                $this->assertNull($book->cover_image);
            }
        }
    }
} 