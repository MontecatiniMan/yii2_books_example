<?php

namespace app\services\interfaces;

use yii\web\UploadedFile;

interface FileUploadServiceInterface
{
    /**
     * Загружает файл обложки книги
     * @param UploadedFile $file Загружаемый файл
     * @param string|null $oldFilePath Путь к старому файлу для удаления
     * @return string|null Путь к загруженному файлу или null при ошибке
     */
    public function uploadBookCover(UploadedFile $file, ?string $oldFilePath = null): ?string;

    /**
     * Удаляет файл обложки
     * @param string $filePath Путь к файлу
     * @return bool Результат удаления
     */
    public function deleteBookCover(string $filePath): bool;

    /**
     * Проверяет существование файла обложки
     * @param string|null $filePath Путь к файлу
     * @return bool Существует ли файл
     */
    public function coverExists(?string $filePath): bool;

    /**
     * Получает URL для отображения обложки или заглушки
     * @param string|null $filePath Путь к файлу обложки
     * @return string URL для отображения
     */
    public function getCoverUrl(?string $filePath): string;
} 