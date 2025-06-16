<?php

declare(strict_types=1);

namespace app\controllers;

use Exception;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\services\interfaces\AuthorServiceInterface;
use app\services\interfaces\SubscriptionServiceInterface;
use app\services\interfaces\BookServiceInterface;
use app\models\Author;
use yii\web\Response;

class AuthorController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly AuthorServiceInterface $authorService,
        private readonly SubscriptionServiceInterface $subscriptionService,
        private readonly BookServiceInterface $bookService,
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
                        'actions' => ['index', 'view', 'subscribe'],
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
        $dataProvider = $this->authorService->getDataProvider();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->authorService->getAuthor($id);
        
        // Получаем URL обложек для всех книг автора
        $bookCoverUrls = [];
        foreach ($model->books as $book) {
            $bookCoverUrls[$book->id] = $this->bookService->getBookCoverUrl($book);
        }
        
        return $this->render('view', [
            'model' => $model,
            'bookCoverUrls' => $bookCoverUrls,
        ]);
    }

    public function actionSubscribe(int $id): Response
    {
        $author = $this->authorService->getAuthor($id);
        
        if (Yii::$app->request->isPost) {
            $phone = Yii::$app->request->post('phone');
            
            if (empty($phone)) {
                Yii::$app->session->setFlash('error', 'Необходимо указать номер телефона');
                return $this->redirect(['view', 'id' => $id]);
            }

            // Валидация формата телефона
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if (!preg_match('/^\+?[78]\d{10}$/', $phone)) {
                Yii::$app->session->setFlash('error', 'Неверный формат номера телефона. Используйте формат +7xxxxxxxxxx');
                return $this->redirect(['view', 'id' => $id]);
            }

            try {
                if ($this->subscriptionService->subscribe($id, $phone, Yii::$app->user->id ?? null)) {
                    Yii::$app->session->setFlash('success', 'Вы успешно подписались на уведомления о новых книгах автора ' . $author->name);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при оформлении подписки. Возможно, этот номер уже подписан на данного автора.');
                }
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', 'Произошла ошибка: ' . $e->getMessage());
            }
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionCreate(): Response|string
    {
        $model = new Author();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $author = $this->authorService->createAuthor($model);

                Yii::$app->session->setFlash('success', 'Автор успешно создан');

                return $this->redirect(['view', 'id' => $author->id]);
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $author = $this->authorService->getAuthor($id);

        $model = new Author();
        $model->name = $author->name;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->authorService->updateAuthor($id, $model);

                Yii::$app->session->setFlash('success', 'Автор успешно обновлен');

                return $this->redirect(['view', 'id' => $id]);
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        try {
            $this->authorService->deleteAuthor($id);

            Yii::$app->session->setFlash('success', 'Автор успешно удален');
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }
} 