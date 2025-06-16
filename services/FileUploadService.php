<?php

declare(strict_types=1);

namespace app\services;

use app\services\interfaces\FileUploadServiceInterface;
use app\services\interfaces\LoggerInterface;
use yii\web\UploadedFile;
use Yii;

class FileUploadService implements FileUploadServiceInterface
{
    private const COVERS_DIRECTORY = 'uploads/covers';
    private const NO_COVER_IMAGE = 'images/no-cover.svg';
    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Загружает файл обложки книги
     */
    public function uploadBookCover(UploadedFile $file, ?string $oldFilePath = null): ?string
    {
        try {
            // Валидация файла
            if (!$this->validateFile($file)) {
                return null;
            }

            // Создаем директорию если не существует
            $uploadDir = Yii::getAlias('@webroot/' . self::COVERS_DIRECTORY);
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $this->logger->error('Не удалось создать директорию для загрузки', [
                        'directory' => $uploadDir
                    ]);
                    return null;
                }
            }

            // Генерируем уникальное имя файла
            $fileName = $this->generateFileName($file->extension);
            $filePath = self::COVERS_DIRECTORY . '/' . $fileName;
            $fullPath = Yii::getAlias('@webroot/' . $filePath);

            // Сохраняем файл
            if (!$file->saveAs($fullPath)) {
                $this->logger->error('Ошибка при сохранении файла обложки', [
                    'fileName' => $fileName,
                    'path' => $fullPath
                ]);
                return null;
            }

            // Удаляем старый файл если он существует
            if ($oldFilePath) {
                $this->deleteBookCover($oldFilePath);
            }

            $this->logger->info('Файл обложки успешно загружен', [
                'fileName' => $fileName,
                'size' => $file->size,
                'originalName' => $file->name
            ]);

            return $filePath;

        } catch (\Throwable $e) {
            $this->logger->error('Неожиданная ошибка при загрузке файла обложки', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Удаляет файл обложки
     */
    public function deleteBookCover(string $filePath): bool
    {
        try {
            $fullPath = Yii::getAlias('@webroot/' . $filePath);
            
            if (!file_exists($fullPath)) {
                return true; // Файл уже не существует
            }

            if (unlink($fullPath)) {
                $this->logger->info('Файл обложки успешно удален', [
                    'filePath' => $filePath
                ]);
                return true;
            } else {
                $this->logger->warning('Не удалось удалить файл обложки', [
                    'filePath' => $filePath
                ]);
                return false;
            }

        } catch (\Throwable $e) {
            $this->logger->error('Ошибка при удалении файла обложки', [
                'filePath' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Проверяет существование файла обложки
     */
    public function coverExists(?string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }

        $fullPath = Yii::getAlias('@webroot/' . $filePath);
        return file_exists($fullPath) && is_file($fullPath);
    }

    /**
     * Получает URL для отображения обложки или заглушки
     */
    public function getCoverUrl(?string $filePath): string
    {
        if (!empty($filePath) && $this->coverExists($filePath)) {
            return Yii::getAlias('@web/' . $filePath);
        }

        return Yii::getAlias('@web/' . self::NO_COVER_IMAGE);
    }

    /**
     * Валидирует загружаемый файл
     */
    private function validateFile(UploadedFile $file): bool
    {
        // Проверка размера файла
        if ($file->size > self::MAX_FILE_SIZE) {
            $this->logger->warning('Файл превышает максимальный размер', [
                'fileSize' => $file->size,
                'maxSize' => self::MAX_FILE_SIZE,
                'fileName' => $file->name
            ]);
            return false;
        }

        // Проверка расширения файла
        $extension = strtolower($file->extension);
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $this->logger->warning('Недопустимое расширение файла', [
                'extension' => $extension,
                'allowedExtensions' => self::ALLOWED_EXTENSIONS,
                'fileName' => $file->name
            ]);
            return false;
        }

        // Проверка MIME типа
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ];

        if (!in_array($file->type, $allowedMimeTypes)) {
            $this->logger->warning('Недопустимый MIME тип файла', [
                'mimeType' => $file->type,
                'allowedMimeTypes' => $allowedMimeTypes,
                'fileName' => $file->name
            ]);
            return false;
        }

        return true;
    }

    /**
     * Генерирует уникальное имя файла
     */
    private function generateFileName(string $extension): string
    {
        return uniqid('cover_', true) . '.' . strtolower($extension);
    }

    /**
     * Получает информацию о допустимых файлах для отображения пользователю
     */
    public function getUploadConstraints(): array
    {
        return [
            'maxSize' => self::MAX_FILE_SIZE,
            'maxSizeMB' => round(self::MAX_FILE_SIZE / (1024 * 1024), 1),
            'allowedExtensions' => self::ALLOWED_EXTENSIONS,
            'allowedExtensionsString' => implode(', ', self::ALLOWED_EXTENSIONS)
        ];
    }
} 