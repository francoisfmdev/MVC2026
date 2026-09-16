<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/** Représentation renvoyée à Twig / JSON (pas la ligne SQL brute telle quelle). */
final class UserOutputDTO extends DTO
{
    public int $id;
    public string $name;
    public string $email;
    public string $created_at;
}
