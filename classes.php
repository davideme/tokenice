<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Enum\Type;
use Aws\DynamoDb\Model;
use Aws\DynamoDb\Model\Attribute;

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
            $objectId = explode('/', $_SERVER['REQUEST_URI'])[4];
            retrieveObject($className, $objectId);
            break;
        case 'PUT':
            $objectId = explode('/', $_SERVER['REQUEST_URI'])[4];
            $input = file_get_contents('php://input');
            updateObject($className, $objectId, $input);
            break;
        case 'DELETE':
            echo $_SERVER['REQUEST_METHOD'];
            break;
    }
}

function createObject($className, $input)
{
    $item = json_decode($input, true);
    $datetime = new DateTime('now', new DateTimeZone('UTC'));
    $item['createdAt'] = $datetime->format(DATE_ISO8601);
    $item['updatedAt'] = $datetime->format(DATE_ISO8601);
    $item['objectId'] = uniqid();

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

function retrieveObject($className, $objectId)
{
    $aws = Aws\Common\Aws::factory("./config.php");
    /** @var $client Aws\DynamoDb\DynamoDbClient */
    $client = $aws->get("dynamodb");

    $response = $client->getItem(
        array(
            "TableName" => $className,
            "ConsistentRead" => true,
            "Key" => array(
                "objectId" => array(Type::STRING => $objectId)
            ),
        )
    );

    $item = array();
    foreach ($response["Item"] as $key => $value) {
        if (array_keys($value)[0] == Type::NUMBER) {
            $item[$key] = (int)array_values($value)[0];
        } else {
            $item[$key] = array_values($value)[0];
        }
    }

    echo json_encode($item, JSON_PRETTY_PRINT);
}

function updateObject($className, $objectId, $input)
{
    $updateItem = array();
    $datetime = new DateTime('now', new DateTimeZone('UTC'));
    $updateItem['updatedAt'] = $datetime->format(DATE_ISO8601);

    $item = json_decode($input, true);
    $item += $updateItem;
    $aws = Aws\Common\Aws::factory("./config.php");
    /** @var $client Aws\DynamoDb\DynamoDbClient */
    $client = $aws->get("dynamodb");

    $response = $client->updateItem(
        array(
            "TableName" => $className,
            "Key" => array(
                "objectId" => array(Type::STRING => $objectId)
            ),
            "AttributeUpdates" => $client->formatAttributes($item, Attribute::FORMAT_UPDATE),
        )
    );

    echo json_encode($updateItem, JSON_PRETTY_PRINT);
}

/**
 * @return string
 */
function currentUrl()
{
    return "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}