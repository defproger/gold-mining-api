<?php
require "db.php";
header('Content-Type: application/json');

$_POST = json_decode(file_get_contents("php://input"), 1) ?? $_POST;

function response($data = null, $errors = false, $code = 200)
{
    http_response_code($code);;
    echo json_encode($data ?? ['errors' => $errors]);
    exit();
}

function api($method, $url, $func)
{
    $pattern = str_replace('/', '\/', $url);
    $pattern = preg_replace('/<(\w+)>/', '(?P<$1>\w+)', $pattern);
    if ($_SERVER['REQUEST_METHOD'] === $method && preg_match('/^' . $pattern . '$/', $_SERVER['REQUEST_URI'], $matches)) {
        $func($matches);
    }
}



response(errors: 'incorrect api request', code: 404);