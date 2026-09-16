<?php

declare(strict_types=1);

use PDO;

/**
 * SQL brut volontaire : pas de schéma abstrait.
 * Exécutée par : php framework migrate
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `email` VARCHAR(190) NOT NULL UNIQUE,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `users`');
    }
};
