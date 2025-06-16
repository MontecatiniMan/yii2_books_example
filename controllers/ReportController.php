<?php

declare(strict_types=1);

namespace app\controllers;

use Exception;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\services\interfaces\ReportServiceInterface;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ReportController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly ReportServiceInterface $reportService,
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
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException('Доступ запрещен. Авторизуйтесь для просмотра отчетов.');
                }
            ],
        ];
    }

    public function actionTopAuthors($year = null): Response|string
    {
        try {
            $year = $year ? (int)$year : null;
            $authors = $this->reportService->getTopAuthorsByYear($year);
            $actualYear = $year ?? (int)date('Y');

            return $this->render('top-authors', [
                'authors' => $authors,
                'year' => $actualYear,
            ]);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['site/index']);
        }
    }
} 