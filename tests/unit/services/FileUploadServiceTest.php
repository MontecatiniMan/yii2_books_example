<?php

namespace tests\unit\services;

use app\services\FileUploadService;
use app\services\interfaces\LoggerInterface;
use Codeception\Test\Unit;
use yii\web\UploadedFile;
use Yii;

class FileUploadServiceTest extends Unit
{
    private FileUploadService $service;
    private LoggerInterface $logger;

    protected function _before()
    {
        $this->logger = $this->makeEmpty(LoggerInterface::class);
        $this->service = new FileUploadService($this->logger);
    }

    public function testGetCoverUrlWithValidPath()
    {
        // Создаем тестовый файл
        $testFile = Yii::getAlias('@webroot/uploads/covers/test.jpg');
        $testDir = dirname($testFile);
        
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        file_put_contents($testFile, 'test content');
        
        $result = $this->service->getCoverUrl('uploads/covers/test.jpg');
        
        $this->assertStringContainsString('/uploads/covers/test.jpg', $result);
        
        // Очищаем
        if (file_exists($testFile)) {
            unlink($testFile);
        }
    }

    public function testGetCoverUrlWithEmptyPath()
    {
        $result = $this->service->getCoverUrl(null);
        
        $this->assertStringContainsString('/images/no-cover.svg', $result);
    }

    public function testGetCoverUrlWithNonExistentFile()
    {
        $result = $this->service->getCoverUrl('uploads/covers/nonexistent.jpg');
        
        $this->assertStringContainsString('/images/no-cover.svg', $result);
    }

    public function testCoverExists()
    {
        // Создаем тестовый файл
        $testFile = Yii::getAlias('@webroot/uploads/covers/test_exists.jpg');
        $testDir = dirname($testFile);
        
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        file_put_contents($testFile, 'test content');
        
        $this->assertTrue($this->service->coverExists('uploads/covers/test_exists.jpg'));
        $this->assertFalse($this->service->coverExists('uploads/covers/nonexistent.jpg'));
        $this->assertFalse($this->service->coverExists(''));
        
        // Очищаем
        if (file_exists($testFile)) {
            unlink($testFile);
        }
    }

    public function testDeleteBookCover()
    {
        // Создаем тестовый файл
        $testFile = Yii::getAlias('@webroot/uploads/covers/test_delete.jpg');
        $testDir = dirname($testFile);
        
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        file_put_contents($testFile, 'test content');
        
        $this->assertTrue(file_exists($testFile));
        $this->assertTrue($this->service->deleteBookCover('uploads/covers/test_delete.jpg'));
        $this->assertFalse(file_exists($testFile));
        
        // Повторное удаление должно вернуть true (файл уже не существует)
        $this->assertTrue($this->service->deleteBookCover('uploads/covers/test_delete.jpg'));
    }

    public function testGetUploadConstraints()
    {
        $constraints = $this->service->getUploadConstraints();
        
        $this->assertIsArray($constraints);
        $this->assertArrayHasKey('maxSize', $constraints);
        $this->assertArrayHasKey('maxSizeMB', $constraints);
        $this->assertArrayHasKey('allowedExtensions', $constraints);
        $this->assertArrayHasKey('allowedExtensionsString', $constraints);
        
        $this->assertEquals(2 * 1024 * 1024, $constraints['maxSize']);
        $this->assertEquals(2.0, $constraints['maxSizeMB']);
        $this->assertContains('jpg', $constraints['allowedExtensions']);
        $this->assertContains('png', $constraints['allowedExtensions']);
    }
} 