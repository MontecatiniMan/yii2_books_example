<?php

declare(strict_types=1);

namespace app\services;

use app\services\interfaces\ReportServiceInterface;
use app\repositories\interfaces\AuthorRepositoryInterface;
use app\services\interfaces\LoggerInterface;
use RuntimeException;
use Throwable;

class ReportService implements ReportServiceInterface
{
    public function __construct(
        private readonly AuthorRepositoryInterface $authorRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function getTopAuthorsByYear(?int $year = null, int $limit = 10): array
    {
        if ($year === null) {
            $year = (int)date('Y');
        }

        try {
            $authors = $this->authorRepository->findTopAuthorsByYear($year, $limit);
            
            $this->logger->info('Получен топ авторов', [
                'year' => $year,
                'limit' => $limit,
                'count' => count($authors)
            ]);

            return $authors;
        } catch (Throwable $th) {
            $this->logger->error('Ошибка при получении топа авторов', [
                'year' => $year,
                'limit' => $limit,
                'error' => $th->getMessage()
            ]);
            throw new RuntimeException('Ошибка при получении отчета: ' . $th->getMessage());
        }
    }
} 