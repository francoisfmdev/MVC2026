<?php

declare(strict_types=1);

namespace Core;

/**
 * Wrapper de $_SESSION.
 * session_start() n'est PAS ici : il est centralisé dans public/index.php.
 *
 * flash() / getFlash() : message lu une seule fois (PRG : Post-Redirect-Get).
 */
final class Session
{
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
