<?php

declare(strict_types=1);

namespace Core;

use PDO;

/**
 * Exécute les fichiers database/migrations/*.php dans l'ordre du nom.
 * Chaque fichier retourne un objet avec up(PDO) / down(PDO) contenant du SQL brut.
 */
final class MigrationRunner
{
    public function __construct(private PDO $pdo)
    {
    }

    public function migrate(): array
    {
        $this->ensureTable();
        $ran = $this->ranFilenames();
        $executed = [];

        foreach ($this->files() as $file) {
            $name = basename($file);
            if (in_array($name, $ran, true)) {
                continue;
            }

            /** @var object{up: callable, down: callable} $migration */
            $migration = require $file;
            $migration->up($this->pdo);

            $stmt = $this->pdo->prepare('INSERT INTO `migrations` (`filename`, `ran_at`) VALUES (?, ?)');
            $stmt->execute([$name, date('Y-m-d H:i:s')]);
            $executed[] = $name;
        }

        return $executed;
    }

    /** @return list<string> */
    private function files(): array
    {
        $dir = BASE_PATH . '/database/migrations';
        $files = glob($dir . '/*.php') ?: [];
        sort($files, SORT_STRING);

        return $files;
    }

    private function ensureTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `filename` VARCHAR(255) NOT NULL UNIQUE,
                `ran_at` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    /** @return list<string> */
    private function ranFilenames(): array
    {
        $stmt = $this->pdo->query('SELECT `filename` FROM `migrations` ORDER BY `id`');
        $rows = $stmt->fetchAll();

        return array_column($rows, 'filename');
    }
}
