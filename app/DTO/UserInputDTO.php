<?php

declare(strict_types=1);

namespace App\DTO;

use Core\DTO;

/** Données du formulaire / POST JSON (création et mise à jour). */
final class UserInputDTO extends DTO
{
    public string $name;
    public string $email;
}
