<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class AuthorSubscriptionFixture extends ActiveFixture
{
    public $modelClass = 'app\models\AuthorSubscription';
    public $dataFile = '@tests/_data/author_subscription.php';
    
    public $depends = [
        UserFixture::class,
        AuthorFixture::class,
    ];
} 