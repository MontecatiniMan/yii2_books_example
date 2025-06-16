<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Book */
/* @var $coverUrl string */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Подключаем стили для обложки
$this->registerCssFile('@web/css/book-cover.css');
?>
<div class="book-view">
    <h1 class="book-title"><?= Html::encode($this->title) ?></h1>

    <div class="book-view-container">
        <!-- Обложка книги слева -->
        <div class="book-cover-container">
            <?= Html::img($coverUrl, [
                'alt' => 'Обложка книги: ' . Html::encode($model->title),
                'class' => 'book-cover',
                'title' => Html::encode($model->title)
            ]) ?>
        </div>

        <!-- Детали книги справа -->
        <div class="book-details-container">
            <?php if (!Yii::$app->user->isGuest): ?>
            <p>
                <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Вы уверены, что хотите удалить эту книгу?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
            <?php endif; ?>

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'title',
                    'description:ntext',
                    'publication_year',
                    'isbn',
                    [
                        'attribute' => 'authors',
                        'value' => function ($model) {
                            return implode(', ', array_map(function($author) {
                                return $author->name;
                            }, $model->authors));
                        },
                    ],
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>

    <h2>Авторы</h2>
    <ul>
        <?php foreach ($model->authors as $author): ?>
            <li><?= Html::encode($author->name) ?></li>
        <?php endforeach; ?>
    </ul>
</div> 