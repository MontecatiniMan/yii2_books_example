<?php

declare(strict_types=1);

namespace app\controllers;

use Exception;
use Throwable;
use Yii;
use app\services\interfaces\ReportServiceInterface;
use yii\web\Response;

class ReportController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ReportServiceInterface $reportService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
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
        } catch (Throwable $th) {
            Yii::$app->session->setFlash('error', $th->getMessage());

            return $this->redirect(['site/index']);
        }
    }
} 