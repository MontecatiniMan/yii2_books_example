<?php

use app\repositories\BookRepository;
use app\repositories\AuthorRepository;
use app\repositories\AuthorSubscriptionRepository;
use app\repositories\interfaces\BookRepositoryInterface;
use app\repositories\interfaces\AuthorRepositoryInterface;
use app\repositories\interfaces\AuthorSubscriptionRepositoryInterface;
use app\services\BookService;
use app\services\AuthorService;
use app\services\ReportService;
use app\services\Logger;
use app\services\SmsService;
use app\services\SubscriptionService;
use app\services\FileUploadService;
use app\services\interfaces\BookServiceInterface;
use app\services\interfaces\AuthorServiceInterface;
use app\services\interfaces\ReportServiceInterface;
use app\services\interfaces\LoggerInterface;
use app\services\interfaces\SmsServiceInterface;
use app\services\interfaces\SubscriptionServiceInterface;
use app\services\interfaces\FileUploadServiceInterface;

return [
    'definitions' => [
        BookRepositoryInterface::class => BookRepository::class,
        AuthorRepositoryInterface::class => AuthorRepository::class,
        AuthorSubscriptionRepositoryInterface::class => AuthorSubscriptionRepository::class,
        BookServiceInterface::class => BookService::class,
        AuthorServiceInterface::class => AuthorService::class,
        ReportServiceInterface::class => ReportService::class,
        LoggerInterface::class => Logger::class,
        SmsServiceInterface::class => SmsService::class,
        SubscriptionServiceInterface::class => SubscriptionService::class,
        FileUploadServiceInterface::class => FileUploadService::class,
    ],
]; 