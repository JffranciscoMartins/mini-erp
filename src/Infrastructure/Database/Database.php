<?php

namespace MiniERP\Infrastructure\Database;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
            $dotenv->load();

            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $name = $_ENV['DB_NAME'] ?? 'mini_erp';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? 'root';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$name;charset=$charset";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
