<?php

declare(strict_types=1);

namespace app\controllers;

use Exception;
use Yii;
use app\services\interfaces\BookServiceInterface;
use app\services\interfaces\AuthorServiceInterface;
use app\services\interfaces\FileUploadServiceInterface;
use app\models\Book;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class BookController extends BaseController
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


    /**
     * @throws ForbiddenHttpException
     */
    public function actionIndex(): string
    {
        $this->requirePermission('viewBooks');
        
        $dataProvider = $this->bookService->getDataProvider();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     */
    public function actionView(int $id): string
    {
        $this->requirePermission('viewBooks');
        
        $model = $this->bookService->getBook($id);
        $coverUrl = $this->bookService->getBookCoverUrl($model);
        
        return $this->render('view', [
            'model' => $model,
            'coverUrl' => $coverUrl,
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     */
    public function actionCreate(): Response|string
    {
        $this->requirePermission('manageBooks');
        
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

    /**
     * @throws ForbiddenHttpException
     */
    public function actionUpdate(int $id): Response|string
    {
        $this->requirePermission('manageBooks');
        
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

    /**
     * @throws ForbiddenHttpException
     */
    public function actionDelete(int $id): Response
    {
        $this->requirePermission('manageBooks');
        
        try {
            $this->bookService->deleteBook($id);

            Yii::$app->session->setFlash('success', 'Книга успешно удалена');
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }
} 