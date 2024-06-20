<?php

namespace App\Repositories;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

class EmailRecipientRepository
{
    protected $dynamoDb;

    public function __construct(DynamoDbClient $dynamoDb)
    {
        $this->dynamoDb = $dynamoDb;
    }

    public function createUser($userId, $email)
    {
        $params = [
            'TableName' => 'users', // Replace with your DynamoDB table name
            'Item' => [
                'user_id' => ['S' => $userId],
                'email' => ['S' => $email],
            ]
        ];

        try {
            $this->dynamoDb->putItem($params);
        } catch (DynamoDbException $e) {
            throw new \Exception("Unable to add user: " . $e->getMessage());
        }
    }

    public function getUser($userId)
    {
        $params = [
            'TableName' => 'users', // Replace with your DynamoDB table name
            'Key' => [
                'user_id' => ['S' => $userId]
            ]
        ];

        try {
            $result = $this->dynamoDb->getItem($params);
            return $result['Item'] ?? null;
        } catch (DynamoDbException $e) {
            throw new \Exception("Unable to get user: " . $e->getMessage());
        }
    }

    // Add more methods as needed
}
