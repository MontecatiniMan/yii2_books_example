<?php

declare(strict_types=1);

namespace app\services;

use app\models\Book;
use app\repositories\interfaces\BookRepositoryInterface;
use app\services\interfaces\BookServiceInterface;
use app\services\interfaces\LoggerInterface;
use app\services\interfaces\SubscriptionServiceInterface;
use app\services\interfaces\FileUploadServiceInterface;
use RuntimeException;
use Throwable;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class BookService implements BookServiceInterface
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository,
        private readonly LoggerInterface $logger,
        private readonly SubscriptionServiceInterface $subscriptionService,
        private readonly FileUploadServiceInterface $fileUploadService
    ) {}

    public function getDataProvider(): ActiveDataProvider
    {
        return $this->bookRepository->getDataProvider();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getBook(int $id): Book
    {
        $book = $this->bookRepository->findById($id);

        if (!$book) {
            $this->logger->error('Книга не найдена', ['id' => $id]);
            throw new NotFoundHttpException('Книга не найдена');
        }

        return $book;
    }

    public function getAllBooks(): array
    {
        return $this->bookRepository->findAll();
    }

    public function createBookWithAuthors(Book $book, array $authorIds): Book
    {
        if (!$this->bookRepository->saveWithAuthors($book, $authorIds)) {
            $this->logger->error('Ошибка при сохранении книги с авторами', [
                'title' => $book->title,
                'authorIds' => $authorIds,
                'errors' => $book->errors
            ]);
            throw new RuntimeException('Ошибка при сохранении книги: ' . implode(', ', $book->getErrorSummary(true)));
        }

        // Перезагружаем книгу с авторами для отправки уведомлений
        $book->refresh();
        $book->authors; // Принудительно загружаем связи

        // Отправляем уведомления о новой книге
        $this->subscriptionService->notifyAboutNewBook($book);

        return $book;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function updateBook(int $id, Book $bookData): Book
    {
        $book = $this->getBook($id);
        $book->title = $bookData->title;
        $book->description = $bookData->description;
        $book->publication_year = $bookData->publication_year;
        $book->isbn = $bookData->isbn;
        $book->cover_image = $bookData->cover_image;

        if (!$this->bookRepository->save($book)) {
            $this->logger->error('Ошибка при обновлении книги', [
                'id' => $id,
                'title' => $bookData->title,
                'errors' => $book->errors
            ]);
            throw new RuntimeException('Ошибка при обновлении книги: ' . implode(', ', $book->getErrorSummary(true)));
        }

        return $book;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function updateBookWithAuthors(int $id, Book $bookData, array $authorIds): Book
    {
        $book = $this->getBook($id);
        $book->title = $bookData->title;
        $book->description = $bookData->description;
        $book->publication_year = $bookData->publication_year;
        $book->isbn = $bookData->isbn;
        $book->cover_image = $bookData->cover_image;

        if (!$this->bookRepository->saveWithAuthors($book, $authorIds)) {
            $this->logger->error('Ошибка при обновлении книги с авторами', [
                'id' => $id,
                'title' => $bookData->title,
                'authorIds' => $authorIds,
                'errors' => $book->errors
            ]);
            throw new RuntimeException('Ошибка при обновлении книги: ' . implode(', ', $book->getErrorSummary(true)));
        }

        return $book;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function deleteBook(int $id): void
    {
        $book = $this->getBook($id);
        if (!$this->bookRepository->delete($book)) {
            $this->logger->error('Ошибка при удалении книги', [
                'id' => $id,
                'title' => $book->title
            ]);
            throw new RuntimeException('Ошибка при удалении книги');
        }
    }

    public function getBooksByAuthor(int $authorId): array
    {
        return $this->bookRepository->findByAuthorId($authorId);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getBookAuthorIds(int $bookId): array
    {
        $book = $this->getBook($bookId);
        return $this->bookRepository->getAuthorIds($book);
    }

    public function getBookCoverUrl(Book $book): string
    {
        $coverPath = $this->bookRepository->getCoverImagePath($book);
        return $this->fileUploadService->getCoverUrl($coverPath);
    }

    public function updateBookCover(Book $book, ?string $coverPath): bool
    {
        try {
            return $this->bookRepository->updateCoverImage($book, $coverPath);
        } catch (Throwable $th) {
            $this->logger->error('Ошибка при обновлении обложки книги', [
                'bookId' => $book->id,
                'coverPath' => $coverPath,
                'error' => $th->getMessage()
            ]);
            return false;
        }
    }
} 