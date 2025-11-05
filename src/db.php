<?php

namespace App\DB;

final class Database
{
    private static ?Database $instance = null;
    private \PDO $connection;

    private function __construct()
    {
        $host = getenv('DATABASE_HOST') ?: 'localhost';
        $dbName = getenv('DB_NAME') ?: 'parque';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'rootpassword';
        $port = getenv('DB_PORT') ?: '3306';

        $dsn = sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4', $host, $dbName, $port);

        $this->connection = new \PDO($dsn, $user, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }

    public function __clone(): void
    {
        throw new \LogicException('Database is a singleton and cannot be cloned.');
    }

    public function __wakeup(): void
    {
        throw new \LogicException('Database is a singleton and cannot be unserialized.');
    }
}
