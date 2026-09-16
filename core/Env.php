<?php

declare(strict_types=1);

namespace Core;

/**
 * Parseur .env maison (pas de vlucas/phpdotenv).
 *
 * Syntaxe acceptée : KEY=value, commentaires #, valeurs entre quotes.
 * À charger une seule fois au boot (public/index.php ou tests/bootstrap.php).
 */
final class Env
{
    /** @var array<string, string> */
    private static array $vars = [];

    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Fichier .env introuvable : {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException("Impossible de lire le fichier .env : {$path}");
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eq));
            $value = trim(substr($line, $eq + 1));

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$vars[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded && !array_key_exists($key, self::$vars)) {
            return $default;
        }

        return self::$vars[$key] ?? $default;
    }

    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
