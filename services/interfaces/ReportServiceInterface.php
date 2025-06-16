<?php

namespace app\services\interfaces;

interface ReportServiceInterface
{
    /**
     * Получить топ авторов по количеству книг за год
     * 
     * @param int|null $year Год для фильтрации (если null, то текущий год)
     * @param int $limit Количество авторов для возврата
     * @return array
     */
    public function getTopAuthorsByYear(?int $year = null, int $limit = 10): array;
} 