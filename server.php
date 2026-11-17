<?php

/**
 * Laravel local development server script for Apizmart
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// 1. Direct file check relative to project root (e.g. /public/frontEnd/...)
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    serveStaticFile(__DIR__ . $uri);
    return true;
}

// 2. Direct file check relative to public folder (e.g. /frontEnd/...)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
    serveStaticFile(__DIR__ . '/public' . $uri);
    return true;
}

function serveStaticFile(string $filePath): void
{
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'json'  => 'application/json',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'otf'   => 'font/otf',
        'pdf'   => 'application/pdf',
        'mp4'   => 'video/mp4',
        'map'   => 'application/json',
    ];

    $mime = $mimes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream');
    header("Content-Type: {$mime}");
    header("Content-Length: " . filesize($filePath));
    readfile($filePath);
    exit;
}

// 3. Fallback to Laravel Front Controller
require_once __DIR__ . '/index.php';
