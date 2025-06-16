<?php

namespace tests\unit\integration;

use app\models\Book;
use app\models\Author;
use app\services\interfaces\BookServiceInterface;
use app\services\interfaces\FileUploadServiceInterface;
use Codeception\Test\Unit;
use Yii;

class BookCoverSimpleTest extends Unit
{
    private BookServiceInterface $bookService;
    private FileUploadServiceInterface $fileUploadService;

    protected function _before()
    {
        $this->bookService = Yii::$container->get(BookServiceInterface::class);
        $this->fileUploadService = Yii::$container->get(FileUploadServiceInterface::class);
    }

    public function testBookWithoutCoverShowsPlaceholder()
    {
        // Создаем автора
        $author = new Author();
        $author->name = 'Тестовый автор';
        $this->assertTrue($author->save());

        // Создаем книгу без обложки
        $book = new Book();
        $book->title = 'Книга без обложки';
        $book->publication_year = 2024;
        $book->cover_image = null;

        $savedBook = $this->bookService->createBookWithAuthors($book, [$author->id]);
        $this->assertNotNull($savedBook->id);
        $this->assertNull($savedBook->cover_image);

        // Проверяем URL обложки (должна быть заглушка)
        $coverUrl = $this->bookService->getBookCoverUrl($savedBook);
        $this->assertStringContainsString('/images/no-cover.svg', $coverUrl);
    }

    public function testBookWithCoverShowsCorrectUrl()
    {
        // Создаем автора
        $author = new Author();
        $author->name = 'Автор с обложкой';
        $this->assertTrue($author->save());

        // Создаем книгу с обложкой
        $book = new Book();
        $book->title = 'Книга с обложкой';
        $book->publication_year = 2024;
        $book->cover_image = 'uploads/covers/test-cover.jpg';

        $savedBook = $this->bookService->createBookWithAuthors($book, [$author->id]);
        $this->assertNotNull($savedBook->id);
        $this->assertEquals('uploads/covers/test-cover.jpg', $savedBook->cover_image);

        // Проверяем URL обложки
        $coverUrl = $this->bookService->getBookCoverUrl($savedBook);
        $this->assertStringContainsString('/images/no-cover.svg', $coverUrl); // Файл не существует, поэтому заглушка
    }

    public function testUpdateBookCover()
    {
        // Создаем автора
        $author = new Author();
        $author->name = 'Автор для обновления';
        $this->assertTrue($author->save());

        // Создаем книгу без обложки
        $book = new Book();
        $book->title = 'Книга для обновления обложки';
        $book->publication_year = 2024;

        $savedBook = $this->bookService->createBookWithAuthors($book, [$author->id]);
        $this->assertNull($savedBook->cover_image);

        // Обновляем обложку
        $newCoverPath = 'uploads/covers/updated-cover.jpg';
        $result = $this->bookService->updateBookCover($savedBook, $newCoverPath);
        $this->assertTrue($result);

        // Проверяем, что обложка обновилась в БД
        $updatedBook = $this->bookService->getBook($savedBook->id);
        $this->assertEquals($newCoverPath, $updatedBook->cover_image);
    }

    public function testFileUploadServiceConstraints()
    {
        $constraints = $this->fileUploadService->getUploadConstraints();
        
        $this->assertIsArray($constraints);
        $this->assertArrayHasKey('maxSize', $constraints);
        $this->assertArrayHasKey('maxSizeMB', $constraints);
        $this->assertArrayHasKey('allowedExtensions', $constraints);
        $this->assertArrayHasKey('allowedExtensionsString', $constraints);
        
        $this->assertGreaterThan(0, $constraints['maxSize']);
        $this->assertEquals(2.0, $constraints['maxSizeMB']);
        $this->assertContains('jpg', $constraints['allowedExtensions']);
        $this->assertContains('png', $constraints['allowedExtensions']);
    }

    public function testCoverExistsMethod()
    {
        // Тест с несуществующим файлом
        $this->assertFalse($this->fileUploadService->coverExists('uploads/covers/nonexistent.jpg'));
        
        // Тест с пустым путем
        $this->assertFalse($this->fileUploadService->coverExists(''));
        $this->assertFalse($this->fileUploadService->coverExists(null));
    }

    public function testGetCoverUrlWithDifferentInputs()
    {
        // Тест с null
        $url = $this->fileUploadService->getCoverUrl(null);
        $this->assertStringContainsString('/images/no-cover.svg', $url);
        
        // Тест с пустой строкой
        $url = $this->fileUploadService->getCoverUrl('');
        $this->assertStringContainsString('/images/no-cover.svg', $url);
        
        // Тест с несуществующим файлом
        $url = $this->fileUploadService->getCoverUrl('uploads/covers/nonexistent.jpg');
        $this->assertStringContainsString('/images/no-cover.svg', $url);
    }
} 