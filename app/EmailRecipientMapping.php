<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;
use BaoPham\DynamoDb\DynamoDbQueryBuilder;

class EmailRecipientMapping extends DynamoDbModel
{
    protected static function boot()
    {
        parent::boot();
    }
}
