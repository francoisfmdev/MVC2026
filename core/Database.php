<?php

declare(strict_types=1);

namespace Core;

use PDO;

/**
 * Singleton PDO MySQL/MariaDB.
 *
 * Une seule connexion par requête HTTP. Config = config/database.php (lui-même via Env).
 * ERRMODE_EXCEPTION + FETCH_ASSOC : erreurs visibles, lignes = tableaux (pas d'objets ORM).
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['name']
            );

            self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$instance;
    }

    /** Utile pour les tests : réinitialise le singleton. */
    public static function reset(): void
    {
        self::$instance = null;
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$instance = $pdo;
    }
}
