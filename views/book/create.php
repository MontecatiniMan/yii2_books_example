<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Book */
/* @var $authors app\models\Author[] */

$this->title = 'Создать книгу';
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Подключаем кастомные стили для Select2
$this->registerCssFile('@web/css/select2-custom.css');
?>

<div class="book-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'publication_year')->textInput(['type' => 'number', 'min' => 1000, 'max' => date('Y')]) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cover_image')->fileInput(['accept' => 'image/*']) ?>

    <div class="form-group">
        <label class="control-label">Авторы</label>
        <?= Select2::widget([
            'name' => 'Book[authorIds]',
            'value' => [],
            'data' => ArrayHelper::map($authors, 'id', 'name'),
            'options' => [
                'placeholder' => 'Начните вводить имя автора для поиска...',
                'multiple' => true,
                'id' => 'book-authors-select'
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => false,
                'maximumInputLength' => 100,
                'minimumInputLength' => 0,
                'closeOnSelect' => false,
                'language' => [
                    'noResults' => new \yii\web\JsExpression('function() { return "Авторы не найдены. <a href=\"/author/create\" target=\"_blank\" class=\"btn btn-xs btn-success\">Создать нового автора</a>"; }'),
                    'searching' => new \yii\web\JsExpression('function() { return "Поиск авторов..."; }'),
                    'loadingMore' => new \yii\web\JsExpression('function() { return "Загрузка..."; }'),
                    'maximumSelected' => new \yii\web\JsExpression('function() { return "Достигнуто максимальное количество выбранных авторов"; }'),
                    'inputTooShort' => new \yii\web\JsExpression('function() { return "Введите минимум 1 символ для поиска"; }'),
                ],
                'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            ],
        ]) ?>
        <small class="help-block">Выберите одного или нескольких авторов. Используйте поиск для быстрого нахождения нужного автора.</small>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Создать', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div> 