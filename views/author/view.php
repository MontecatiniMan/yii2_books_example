<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Author */
/* @var $bookCoverUrls array */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (Yii::$app->user->can('manageAuthors')): ?>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить этого автора?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name:text:Имя',
            'created_at:datetime:Создан',
            'updated_at:datetime:Обновлен',
        ],
    ]) ?>

    <div class="subscription-form" style="margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h3>Подписаться на новые книги автора</h3>
        <p>Получайте SMS уведомления о новых книгах этого автора!</p>
        <?php $form = ActiveForm::begin(['action' => ['subscribe', 'id' => $model->id]]); ?>
            <div class="form-group">
                <label for="phone">Номер телефона:</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+7 (xxx) xxx-xx-xx" required>
                <small class="form-text text-muted">Введите номер телефона в формате +7xxxxxxxxxx</small>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Подписаться на уведомления', ['class' => 'btn btn-success']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>

    <h2 style="margin-top: 30px;">Книги автора</h2>
    <?php if (!empty($model->books)): ?>
        <div class="row">
            <?php foreach ($model->books as $book): ?>
                <div class="col-md-4" style="margin-bottom: 20px;">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h4><?= Html::encode($book->title) ?></h4>
                            <div style="text-align: center; margin-bottom: 15px;">
                                <?= Html::img($bookCoverUrls[$book->id], [
                                    'alt' => 'Обложка книги: ' . Html::encode($book->title),
                                    'class' => 'img-responsive',
                                    'style' => 'max-height: 150px; max-width: 100px; border: 1px solid #ddd; border-radius: 4px;'
                                ]) ?>
                            </div>
                            <p>
                                <?= Html::encode(mb_substr($book->description ?? '', 0, 100)) ?>
                                <?= mb_strlen($book->description ?? '') > 100 ? '...' : '' ?>
                            </p>
                            <p>
                                <strong>Год издания:</strong> <?= $book->publication_year ?><br>
                                <?php if ($book->isbn): ?>
                                    <strong>ISBN:</strong> <?= Html::encode($book->isbn) ?>
                                <?php endif; ?>
                            </p>
                            <?= Html::a('Просмотр книги', ['book/view', 'id' => $book->id], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p>У этого автора пока нет книг в каталоге.</p>
        </div>
    <?php endif; ?>
</div> 