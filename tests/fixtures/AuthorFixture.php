<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class AuthorFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Author';
    public $dataFile = '@tests/_data/author.php';
    
    public $depends = [
        'tests\fixtures\UserFixture',
    ];
} 