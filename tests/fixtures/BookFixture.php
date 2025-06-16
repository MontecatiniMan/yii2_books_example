<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class BookFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Book';
    public $dataFile = '@tests/_data/book.php';
    
    public $depends = [
        'tests\fixtures\AuthorFixture',
    ];
} 