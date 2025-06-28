<?php
$host = 'localhost';
$dbname = 'christ_performing_christian_centre'; // Please replace with your actual database name
$username = 'root';
$password = ''; // Common for local XAMPP, replace if you have a password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // For a production environment, you would log this error and show a user-friendly message.
    // For development, throwing the exception provides detailed error information.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}