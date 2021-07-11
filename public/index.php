<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/App.php';

try {
    $app->run();
} catch (Throwable $e) {
    $app->getContainer()->get('logger')->addCritical('we have bug!' . $e->getMessage());
}