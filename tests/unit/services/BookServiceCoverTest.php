<?php

namespace tests\unit\services;

use app\models\Book;
use app\services\BookService;
use app\repositories\interfaces\BookRepositoryInterface;
use app\services\interfaces\LoggerInterface;
use app\services\interfaces\SubscriptionServiceInterface;
use app\services\interfaces\FileUploadServiceInterface;
use Codeception\Test\Unit;

class BookServiceCoverTest extends Unit
{
    private BookService $bookService;
    private BookRepositoryInterface $bookRepository;
    private LoggerInterface $logger;
    private SubscriptionServiceInterface $subscriptionService;
    private FileUploadServiceInterface $fileUploadService;

    /**
     * @throws \Exception
     */
    protected function _before(): void
    {
        $this->bookRepository = $this->makeEmpty(BookRepositoryInterface::class);
        $this->logger = $this->makeEmpty(LoggerInterface::class);
        $this->subscriptionService = $this->makeEmpty(SubscriptionServiceInterface::class);
        $this->fileUploadService = $this->makeEmpty(FileUploadServiceInterface::class);

        $this->bookService = new BookService(
            $this->bookRepository,
            $this->logger,
            $this->subscriptionService,
            $this->fileUploadService
        );
    }

    public function testGetBookCoverUrl()
    {
        // Создаем тестовую книгу
        $book = new Book();
        $book->cover_image = 'uploads/covers/test.jpg';

        // Настраиваем моки
        $this->bookRepository->expects($this->once())
            ->method('getCoverImagePath')
            ->with($book)
            ->willReturn('uploads/covers/test.jpg');

        $this->fileUploadService->expects($this->once())
            ->method('getCoverUrl')
            ->with('uploads/covers/test.jpg')
            ->willReturn('/web/uploads/covers/test.jpg');

        // Тестируем
        $result = $this->bookService->getBookCoverUrl($book);
        $this->assertEquals('/web/uploads/covers/test.jpg', $result);
    }

    public function testGetBookCoverUrlWithNullCover()
    {
        // Создаем тестовую книгу без обложки
        $book = new Book();
        $book->cover_image = null;

        // Настраиваем моки
        $this->bookRepository->expects($this->once())
            ->method('getCoverImagePath')
            ->with($book)
            ->willReturn(null);

        $this->fileUploadService->expects($this->once())
            ->method('getCoverUrl')
            ->with(null)
            ->willReturn('/web/images/no-cover.svg');

        // Тестируем
        $result = $this->bookService->getBookCoverUrl($book);
        $this->assertEquals('/web/images/no-cover.svg', $result);
    }

    public function testUpdateBookCoverSuccess()
    {
        // Создаем тестовую книгу
        $book = new Book();
        $book->id = 1;
        $coverPath = 'uploads/covers/new-cover.jpg';

        // Настраиваем мок репозитория
        $this->bookRepository->expects($this->once())
            ->method('updateCoverImage')
            ->with($book, $coverPath)
            ->willReturn(true);

        // Тестируем
        $result = $this->bookService->updateBookCover($book, $coverPath);
        $this->assertTrue($result);
    }

    public function testUpdateBookCoverFailure()
    {
        // Создаем тестовую книгу
        $book = new Book();
        $book->id = 1;
        $coverPath = 'uploads/covers/new-cover.jpg';

        // Настраиваем мок репозитория для выброса исключения
        $this->bookRepository->expects($this->once())
            ->method('updateCoverImage')
            ->with($book, $coverPath)
            ->willThrowException(new \Exception('Database error'));

        // Настраиваем мок логгера
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Ошибка при обновлении обложки книги',
                $this->callback(function($context) use ($book, $coverPath) {
                    return $context['bookId'] === $book->id &&
                           $context['coverPath'] === $coverPath &&
                           $context['error'] === 'Database error';
                })
            );

        // Тестируем
        $result = $this->bookService->updateBookCover($book, $coverPath);
        $this->assertFalse($result);
    }

    public function testCreateBookWithAuthorsPreservesCoverImage()
    {
        // Создаем тестовую книгу с обложкой
        $book = new Book();
        $book->title = 'Test Book';
        $book->cover_image = 'uploads/covers/test.jpg';
        $authorIds = [1, 2];

        // Настраиваем мок репозитория
        $this->bookRepository->expects($this->once())
            ->method('saveWithAuthors')
            ->with($book, $authorIds)
            ->willReturn(true);

        // Настраиваем мок для уведомлений
        $this->subscriptionService->expects($this->once())
            ->method('notifyAboutNewBook')
            ->with($book);

        // Тестируем
        $result = $this->bookService->createBookWithAuthors($book, $authorIds);
        $this->assertInstanceOf(Book::class, $result);
        $this->assertEquals('uploads/covers/test.jpg', $result->cover_image);
    }

    public function testUpdateBookWithAuthorsCopiesCoverImage()
    {
        // Создаем существующую книгу
        $existingBook = new Book();
        $existingBook->id = 1;
        $existingBook->title = 'Old Title';
        $existingBook->cover_image = 'uploads/covers/old.jpg';

        // Создаем данные для обновления
        $bookData = new Book();
        $bookData->title = 'New Title';
        $bookData->description = 'New Description';
        $bookData->publication_year = 2024;
        $bookData->isbn = '1234567890';
        $bookData->cover_image = 'uploads/covers/new.jpg';

        $authorIds = [1, 2];

        // Настраиваем мок репозитория
        $this->bookRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingBook);

        $this->bookRepository->expects($this->once())
            ->method('saveWithAuthors')
            ->with($this->callback(function($book) use ($bookData) {
                return $book->title === $bookData->title &&
                       $book->description === $bookData->description &&
                       $book->publication_year === $bookData->publication_year &&
                       $book->isbn === $bookData->isbn &&
                       $book->cover_image === $bookData->cover_image;
            }), $authorIds)
            ->willReturn(true);

        // Тестируем
        $result = $this->bookService->updateBookWithAuthors(1, $bookData, $authorIds);
        $this->assertInstanceOf(Book::class, $result);
        $this->assertEquals('New Title', $result->title);
        $this->assertEquals('uploads/covers/new.jpg', $result->cover_image);
    }

    public function testUpdateBookCopiesCoverImage()
    {
        // Создаем существующую книгу
        $existingBook = new Book();
        $existingBook->id = 1;
        $existingBook->title = 'Old Title';
        $existingBook->cover_image = 'uploads/covers/old.jpg';

        // Создаем данные для обновления
        $bookData = new Book();
        $bookData->title = 'New Title';
        $bookData->description = 'New Description';
        $bookData->publication_year = 2024;
        $bookData->isbn = '1234567890';
        $bookData->cover_image = 'uploads/covers/new.jpg';

        // Настраиваем мок репозитория
        $this->bookRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingBook);

        $this->bookRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function($book) use ($bookData) {
                return $book->title === $bookData->title &&
                       $book->description === $bookData->description &&
                       $book->publication_year === $bookData->publication_year &&
                       $book->isbn === $bookData->isbn &&
                       $book->cover_image === $bookData->cover_image;
            }))
            ->willReturn(true);

        // Тестируем
        $result = $this->bookService->updateBook(1, $bookData);
        $this->assertInstanceOf(Book::class, $result);
        $this->assertEquals('New Title', $result->title);
        $this->assertEquals('uploads/covers/new.jpg', $result->cover_image);
    }
} 