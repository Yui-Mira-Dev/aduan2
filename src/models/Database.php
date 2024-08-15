<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

function getPDOInstance()
{
    // Primary database credentials
    $dbHostPrimary = $_ENV['DB_HOST_PRIMARY'];
    $dbNamePrimary = $_ENV['DB_DATABASE_PRIMARY'];
    $dbUserPrimary = $_ENV['DB_USERNAME_PRIMARY'];
    $dbPassPrimary = $_ENV['DB_PASSWORD_PRIMARY'];

    // Backup database credentials
    $dbHostBackup = $_ENV['DB_HOST_BACKUP'];
    $dbNameBackup = $_ENV['DB_DATABASE_BACKUP'];
    $dbUserBackup = $_ENV['DB_USERNAME_BACKUP'];
    $dbPassBackup = $_ENV['DB_PASSWORD_BACKUP'];

    try {
        // Attempt to connect to the primary database
        $pdo = new PDO("mysql:host={$dbHostPrimary};dbname={$dbNamePrimary}", $dbUserPrimary, $dbPassPrimary);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // If primary database connection fails, attempt to connect to the backup database
        try {
            $pdo = new PDO("mysql:host={$dbHostBackup};dbname={$dbNameBackup}", $dbUserBackup, $dbPassBackup);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }
}

try {
    $pdo = getPDOInstance();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
