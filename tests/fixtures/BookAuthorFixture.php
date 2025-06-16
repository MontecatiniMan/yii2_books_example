<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class BookAuthorFixture extends ActiveFixture
{
    public $modelClass = 'app\models\BookAuthor';
    public $dataFile = '@tests/_data/book_author.php';
    
    public $depends = [
        BookFixture::class,
        AuthorFixture::class,
    ];
} 