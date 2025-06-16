<?php

declare(strict_types=1);

namespace app\services;

use app\models\Author;
use app\repositories\interfaces\AuthorRepositoryInterface;
use app\repositories\interfaces\BookRepositoryInterface;
use app\services\interfaces\AuthorServiceInterface;
use app\services\interfaces\LoggerInterface;
use RuntimeException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class AuthorService implements AuthorServiceInterface
{
    public function __construct(
        private readonly AuthorRepositoryInterface $authorRepository,
        private readonly BookRepositoryInterface $bookRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function getDataProvider(): ActiveDataProvider
    {
        return $this->authorRepository->getDataProvider();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getAuthor(int $id): Author
    {
        $author = $this->authorRepository->findById($id);
        if (!$author) {
            $this->logger->error('Автор не найден', ['id' => $id]);
            throw new NotFoundHttpException('Автор не найден');
        }
        return $author;
    }

    public function getAllAuthors(): array
    {
        return $this->authorRepository->findAll();
    }

    public function createAuthor(Author $author): Author
    {
        if (!$this->authorRepository->save($author)) {
            $this->logger->error('Ошибка при сохранении автора', [
                'name' => $author->name,
                'errors' => $author->errors
            ]);
            throw new RuntimeException('Ошибка при сохранении автора');
        }

        return $author;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function updateAuthor(int $id, Author $authorData): Author
    {
        $author = $this->getAuthor($id);
        $author->name = $authorData->name;

        if (!$this->authorRepository->save($author)) {
            $this->logger->error('Ошибка при обновлении автора', [
                'id' => $id,
                'name' => $authorData->name,
                'errors' => $author->errors
            ]);
            throw new RuntimeException('Ошибка при обновлении автора');
        }

        return $author;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function deleteAuthor(int $id): void
    {
        $author = $this->getAuthor($id);
        if (!$this->authorRepository->delete($author)) {
            $this->logger->error('Ошибка при удалении автора', [
                'id' => $id,
                'name' => $author->name
            ]);
            throw new RuntimeException('Ошибка при удалении автора');
        }
    }

    public function getTopAuthorsByYear(int $year, int $limit = 10): array
    {
        return $this->authorRepository->findTopAuthorsByYear($year, $limit);
    }

    public function getAuthorBooks(int $authorId): array
    {
        return $this->bookRepository->findByAuthorId($authorId);
    }
} 