<?php
ini_set('display_errors', 1);
require "db.php";
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), 1) ?? $_POST;

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


/// ROUTES
///
/// Companies
api('GET', '/api/companies/list', function () {
    $list = queryAll("SELECT cm.*, c.name as country_name FROM companies cm left join countries c on cm.country_id = c.id");
    response($list);
});
api('POST', '/api/companies', function () use ($data) {
    $errors = [];
    if (empty($data['name'])) {
        $errors['name'] = 'field is required';
    }
    if (empty($data['country_id'])) {
        $errors['country_id'] = 'field is required';
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'incorrect email';
    }
    if ($errors) {
        response(errors: $errors, code: 400);
    }
    db_insert('companies', [
        'name' => $data['name'],
        'country_id' => $data['country_id'],
        'email' => $data['email'],
    ]);
    response(code: 201);
});
api('POST', '/api/companies/edit/<id>', function ($request) use ($data) {
    $company = db_getById('companies', $request['id']);

    $errors = [];
    if (!$company) {
        $errors['id'] = 'not found';
    }
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'incorrect email';
    }
    if ($errors) {
        response(errors: $errors, code: 400);
    }


    $update = [];
    $bannedColumns = ["id"];
    foreach ($data as $column => $datum) {
        if (array_key_exists($column, $company) && $company[$column] != $datum && !in_array($column, $bannedColumns)) {
            $update[$column] = $datum;
        }
    }
    if (count($update) > 0) {
        db_update('companies', $company['id'], $update);
        response();
    } else
        response(errors: ['fields' => 'no data to update'], code: 400);
});
api('DELETE', '/api/companies/<id>', function ($request) {
    $company = db_getById('companies', $request['id']);
    if (!$company) {
        response(errors: ['id' => 'not found'], code: 404);
    }
    db_delete('companies', $company['id']);
    response(code: 204);
});
///
/// Countries
api('GET', '/api/countries/list', function () {
    $list = queryAll("SELECT * FROM countries");
    response($list);
});
api('POST', '/api/countries', function () use ($data) {
    $errors = [];
    if (empty($data['name'])) {
        $errors['name'] = 'field is required';
    }
    if (!is_numeric($data['plan']) || $data['plan'] < 0) {
        $errors['plan'] = 'incorrect field';
    }
    if ($errors) {
        response(errors: $errors, code: 400);
    }
    db_insert('countries', [
        'name' => $data['name'],
        'plan' => $data['plan'],
    ]);
    response(code: 201);
});
api('POST', '/api/countries/edit/<id>', function ($request) use ($data) {
    $country = db_getById('countries', $request['id']);

    $errors = [];
    if (!$country) {
        $errors['id'] = 'not found';
    }
    if (!is_numeric($data['plan']) || $data['plan'] < 0) {
        $errors['plan'] = 'incorrect field';
    }
    if ($errors) {
        response(errors: $errors, code: 400);
    }

    $update = [];
    $bannedColumns = ["id"];
    foreach ($data as $column => $datum) {
        if (array_key_exists($column, $country) && $country[$column] != $datum && !in_array($column, $bannedColumns)) {
            $update[$column] = $datum;
        }
    }
    if (count($update) > 0) {
        db_update('countries', $country['id'], $update);
        response();
    } else
        response(errors: ['fields' => 'no data to update'], code: 400);
});
api('DELETE', '/api/countries/<id>', function ($request) {
    $country = db_getById('countries', $request['id']);
    if (!$country) {
        response(errors: ['id' => 'not found'], code: 404);
    }
    db_delete('countries', $country['id']);
    response(code: 204);
});
///
/// Reports
///
api('GET', '/api/reports/<month>', function ($request) {
    if ($request['month'] <= date('m')) {
        $year = date('Y');
    } else {
        $year = date('Y') - 1;
    }
    $dateFrom = date("$year-{$request['month']}-01 00:00:00");
    $dateTo = date('Y-m-d 00:00:00', strtotime("$dateFrom +1 month"));

    $reports = queryAll("SELECT m.*, c.id as country_id, c.name as country, c.plan as plan
         FROM mining m
         left join companies cm on cm.id = m.company_id
         left join countries c on c.id = cm.country_id
         WHERE m.date > :dateFrom and m.date < :dateTo",
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]);

    $sortedData = [];
    foreach ($reports as $report) {
        $sortedData[$report['country_id']] = [
            'id' => $report['country_id'],
            'country' => $report['country'],
            'plan' => $report['plan'],
            'mined' => 0,
        ];
    }
    foreach ($reports as $report) {
        $sortedData[$report['country_id']]['mined'] += $report['mined'];
    }
    usort($sortedData, function ($a, $b) {
        return $b['mined'] - $a['mined'];
    });
    response($sortedData);
});
/// OTHER
api('POST', '/api/generate', function () {
    $companies = db_getAll('companies');

    $generated = [];
    $start = strtotime('-6 months');
    $end = time();

    foreach ($companies as $company) {
        for ($i = 0; $i < rand(50, 500); $i++) {
            $mined = rand(100, 10000);
            $date = date('Y-m-d H:i:s', rand($start, $end));
            $generated[] = [
                'company_id' => $company['id'],
                'mined' => $mined,
                'date' => $date,
            ];
        }
    }
    foreach ($generated as $item) {
        db_insert('mining', $item);
    }
    response();
});


response(errors: 'incorrect api request', code: 404);