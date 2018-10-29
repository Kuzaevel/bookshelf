<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
//use Slim\Container;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';

$app = new \Slim\App(array("settings" => $settings['settings']));

$container = $app->getContainer();

$container['view'] = function () {
    $templates =  '../templates/';
    $view = new Slim\Views\Twig($templates);
    return $view;
};

$container['database'] = function ($c) {
    $settings = $c->get('db');
    $dbConection = new PDO($settings['driver'].":host=" . $settings['host'] . ";dbname=" . $settings['dbname'],
        $settings['user'], $settings['pass']);
    $dbConection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbConection;
};

$container['App\Controller'] = function ($c) {
    return new App\Controller($c);
};

$app->get('/', 'App\Controller:listBooks');
$app->get('/add', 'App\Controller:newBook');
$app->post('/add', 'App\Controller:newBook');
$app->get('/delete/{id}', 'App\Controller:deleteBook');
$app->get('/view/{id}', 'App\Controller:viewBook');
$app->get('/edit/{id}', 'App\Controller:editBook');
$app->post('/edit/{id}', 'App\Controller:editBook');

$app->post('/', 'App\Controller:backToIndex');

$container['db'] = $settings["db"];



$app->run();
