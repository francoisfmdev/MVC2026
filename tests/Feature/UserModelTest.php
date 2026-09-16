<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Core\Database;
use PDOException;
use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::reset();

        try {
            $pdo = Database::connection();
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(190) NOT NULL UNIQUE,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ');
        } catch (PDOException $e) {
            $this->markTestSkipped('Base MySQL de test indisponible : ' . $e->getMessage());
        }
    }

    public function testInsertAndFind(): void
    {
        $email = 'ada-' . bin2hex(random_bytes(4)) . '@example.com';

        $id = User::table()->insert([
            'name' => 'Ada Lovelace',
            'email' => $email,
        ]);

        $this->assertGreaterThan(0, $id);

        $user = User::find($id);
        $this->assertNotNull($user);
        $this->assertSame('Ada Lovelace', $user['name']);
        $this->assertSame($email, $user['email']);
    }
}
