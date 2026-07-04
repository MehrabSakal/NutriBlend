<?php
/**
 * Database connection (PDO)
 * FreshSip Beverages - Juice Bar Management System (JMS)
 *
 * Uses PDO for prepared statements / protection against SQL injection.
 * Adjust the credentials below to match your local XAMPP/WAMP setup.
 */

$JMS_DB = [
    'host'    => '127.0.0.1',
    'port'    => '3306',
    'name'    => 'jms_db',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];

try {
    $dsn = "mysql:host={$JMS_DB['host']};port={$JMS_DB['port']};dbname={$JMS_DB['name']};charset={$JMS_DB['charset']}";
    $pdo = new PDO($dsn, $JMS_DB['user'], $JMS_DB['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage())
        . '<br><br>Make sure MySQL is running and that you created the <b>jms_db</b> database '
        . 'from <code>database/schema.sql</code>.');
}
