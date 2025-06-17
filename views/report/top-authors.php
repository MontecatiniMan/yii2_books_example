<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $authors app\models\Author[] */
/* @var $year integer */

$this->title = "Топ-10 авторов за $year год";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-top-authors">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-12">
            <form method="get" class="form-inline mb-4">
                <div class="form-group">
                    <label for="year">Год:</label>
                    <input type="number" class="form-control" id="year" name="year" value="<?= $year ?>" max="<?= date('Y') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Показать</button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Автор</th>
                    <th>Количество книг</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $author): ?>
                    <tr>
                        <td><?= Html::encode($author->name) ?></td>
                        <td><?= $author->book_count ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 