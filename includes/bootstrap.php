<?php
/**
 * Bootstrap - included by every page.
 * Starts the session, loads DB + helper functions and computes BASE_URL
 * so that links work no matter which sub-folder a feature lives in.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/db.php';
require_once APP_ROOT . '/includes/functions.php';

/**
 * Work out the web path of the project root relative to the document root.
 * e.g. project in  htdocs/ISD_Project  =>  BASE_URL = "/ISD_Project"
 */
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $appRoot = str_replace('\\', '/', APP_ROOT);
    $base    = '';
    if ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) {
        $base = rtrim(substr($appRoot, strlen($docRoot)), '/');
    }
    define('BASE_URL', $base);
}

define('APP_NAME', 'FreshSip JMS');

// $1 spent == 1 loyalty point, 100 points == $10 discount (i.e. 10 points = $1).
define('POINTS_PER_DOLLAR', 1);
define('POINTS_PER_DOLLAR_REDEEM', 10);
