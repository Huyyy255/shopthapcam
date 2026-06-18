<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#\.php$#', $requestPath) && $requestPath !== '/api/index.php') {
    $file = realpath(__DIR__ . '/..' . $requestPath);
    $allowedDirs = [realpath(__DIR__ . '/../ajaxs'), realpath(__DIR__ . '/../cron'), realpath(__DIR__ . '/../')];
    foreach ($allowedDirs as $dir) {
        if ($file && strpos($file, $dir) === 0 && file_exists($file)) {
            require $file;
            exit;
        }
    }
}
require_once __DIR__ . '/../index.php';
