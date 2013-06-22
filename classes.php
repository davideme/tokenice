<?php

use Aws\DynamoDb\DynamoDbClient;

header('Content-Type: text/plain; charset=utf-8');
require 'vendor/autoload.php';

routing();

function routing()
{
    $className = explode('/', $_SERVER['REQUEST_URI'])[3];

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $input = file_get_contents('php://input');
            createObject($className, $input);
            break;
        case 'GET':
            echo $_SERVER['REQUEST_METHOD'];
            break;
        case 'PUT':
            echo $_SERVER['REQUEST_METHOD'];
            break;
        case 'DELETE':
            echo $_SERVER['REQUEST_METHOD'];
            break;
    }
}

function createObject($className, $input)
{
    $item = json_decode($input, true);
    $item['objectId'] = uniqid();
    $datetime = new DateTime('now', new DateTimeZone('UTC'));
    $item['createdAt'] = $datetime->format(DATE_ISO8601);

    $aws = Aws\Common\Aws::factory("./config.php");
    /** @var $client Aws\DynamoDb\DynamoDbClient */
    $client = $aws->get("dynamodb");

    $client->putItem(
        array(
            "TableName" => $className,
            "Item" => $client->formatAttributes($item)
        )
    );


    header('Status: 201 Created');
    header('Location: ' . currentUrl() . '/' . $item['objectId']);
    $response = new stdClass();
    $response->createdAt = $item['createdAt'];
    $response->objectId = $item['objectId'];
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * @return string
 */
function currentUrl()
{
    return "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}