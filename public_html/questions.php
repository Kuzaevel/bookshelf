<?php



session_start();

$settings = require __DIR__ . '/../src/settings.php';
$mysqli = new mysqli($settings['host'], $settings['user'], $settings['pass'], $settings['dbname']);