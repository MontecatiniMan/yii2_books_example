<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Каталог книг';
?>
<div class="site-index">
    <div class="jumbotron">
        <h1>Добро пожаловать в каталог книг!</h1>
        <p class="lead">Это приложение для управления каталогом книг и авторов.</p>
        <?php if (Yii::$app->user->isGuest): ?>
            <p class="lead">Вы можете просматривать книги и авторов, а также подписываться на новинки!</p>
        <?php endif; ?>
    </div>

    <div class="body-content">
        <div class="row">
            <div class="col-lg-4">
                <h2>Книги</h2>
                <p>Просмотр каталога книг с информацией о названии, годе издания, описании и авторах.</p>
                <p><?= Html::a('Перейти к книгам &raquo;', ['/book/index'], ['class' => 'btn btn-default']) ?></p>
            </div>
            <div class="col-lg-4">
                <h2>Авторы</h2>
                <p>Просмотр списка авторов и их книг. Возможность подписки на новинки авторов.</p>
                <p><?= Html::a('Перейти к авторам &raquo;', ['/author/index'], ['class' => 'btn btn-default']) ?></p>
            </div>
            <div class="col-lg-4">
                <h2>Отчеты</h2>
                <p>Статистика и аналитика по авторам и их публикациям за различные периоды.</p>
                <p><?= Html::a('ТОП-10 авторов года &raquo;', ['/report/top-authors'], ['class' => 'btn btn-default']) ?></p>
            </div>
        </div>

        <?php if (Yii::$app->user->can('manageBooks') || Yii::$app->user->can('manageAuthors')): ?>
        <div class="row" style="margin-top: 30px;">
            <div class="col-lg-12">
                <h3>Управление (только для авторизованных пользователей)</h3>
                <div class="row">
                    <?php if (Yii::$app->user->can('manageBooks')): ?>
                    <div class="col-lg-6">
                        <h4>Добавить новую книгу</h4>
                        <p>Создание записи о новой книге с указанием авторов, года издания и другой информации.</p>
                        <p><?= Html::a('Добавить книгу &raquo;', ['/book/create'], ['class' => 'btn btn-success']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('manageAuthors')): ?>
                    <div class="col-lg-6">
                        <h4>Добавить автора</h4>
                        <p>Создание профиля нового автора с биографической информацией.</p>
                        <p><?= Html::a('Добавить автора &raquo;', ['/author/create'], ['class' => 'btn btn-success']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
