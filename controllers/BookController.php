<?php

declare(strict_types=1);

namespace app\controllers;

use Exception;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\services\interfaces\BookServiceInterface;
use app\services\interfaces\AuthorServiceInterface;
use app\services\interfaces\FileUploadServiceInterface;
use app\models\Book;
use yii\web\Response;
use yii\web\UploadedFile;

class BookController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly BookServiceInterface $bookService,
        private readonly AuthorServiceInterface $authorService,
        private readonly FileUploadServiceInterface $fileUploadService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = $this->bookService->getDataProvider();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->bookService->getBook($id);
        $coverUrl = $this->bookService->getBookCoverUrl($model);
        
        return $this->render('view', [
            'model' => $model,
            'coverUrl' => $coverUrl,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $model = new Book();
        $authors = $this->authorService->getAllAuthors();


        if ($model->load(Yii::$app->request->post())) {
            // Обрабатываем загрузку обложки
            $coverFile = UploadedFile::getInstance($model, 'cover_image');
            if ($coverFile) {
                $uploadedPath = $this->fileUploadService->uploadBookCover($coverFile);
                if ($uploadedPath) {
                    $model->cover_image = $uploadedPath;
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при загрузке обложки');
                }
            }
            
            if ($model->validate()) {
                try {
                    $authorIds = Yii::$app->request->post('Book')['authorIds'] ?? [];
                    $book = $this->bookService->createBookWithAuthors($model, $authorIds);

                    Yii::$app->session->setFlash('success', 'Книга успешно создана');

                    return $this->redirect(['view', 'id' => $book->id]);
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'authors' => $authors,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->bookService->getBook($id);
        $authors = $this->authorService->getAllAuthors();
        $currentAuthorIds = $this->bookService->getBookAuthorIds($id);
        $oldCoverImage = $model->cover_image;

        if ($model->load(Yii::$app->request->post())) {
            // Обрабатываем загрузку новой обложки
            $coverFile = UploadedFile::getInstance($model, 'cover_image');
            if ($coverFile) {
                $uploadedPath = $this->fileUploadService->uploadBookCover($coverFile, $oldCoverImage);
                if ($uploadedPath) {
                    $model->cover_image = $uploadedPath;
                } else {
                    $model->cover_image = $oldCoverImage; // Возвращаем старое значение при ошибке
                    Yii::$app->session->setFlash('error', 'Ошибка при загрузке обложки');
                }
            } else {
                $model->cover_image = $oldCoverImage; // Сохраняем старое значение если файл не загружен
            }
            
            if ($model->validate()) {
                try {
                    $authorIds = Yii::$app->request->post('Book')['authorIds'] ?? [];
                    $this->bookService->updateBookWithAuthors($id, $model, $authorIds);

                    Yii::$app->session->setFlash('success', 'Книга успешно обновлена');

                    return $this->redirect(['view', 'id' => $id]);
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'authors' => $authors,
            'currentAuthorIds' => $currentAuthorIds,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        try {
            $this->bookService->deleteBook($id);

            Yii::$app->session->setFlash('success', 'Книга успешно удалена');
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }
} 