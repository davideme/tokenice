<?php
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
    json_decode($input);

    $objectId = "Ed1nuqPvcm";

    header('Status: 201 Created');
    header('Location: ' . currentUrl() . '/' . $objectId);
    $response = new stdClass();
    $response->createdAt = "2011-08-20T02:06:57.931Z";
    $response->objectId = $objectId;
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * @return string
 */
function currentUrl()
{
    return "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}