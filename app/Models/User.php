<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Modèle de la table `users`.
 * Pas de relations, pas d'attributs mappés : User::all() renvoie des tableaux SQL.
 */
final class User extends Model
{
    protected static string $table = 'users';
}
