<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Env;
use Core\HttpException;
use Core\Middleware;
use Core\Request;
use Core\Session;

/**
 * Web : session user_id. API : Authorization: Bearer {API_TOKEN du .env}.
 *
 * Exemple pédagogique — à brancher dans config/routes.php :
 *   ['middleware' => [AuthMiddleware::class]]
 * Non appliqué au CRUD users de démo pour pouvoir tester sans login.
 */
final class AuthMiddleware implements Middleware
{
    public function __construct(private Session $session)
    {
    }

    public function handle(Request $request, callable $next): mixed
    {
        if ($request->isApi()) {
            $header = (string) $request->header('Authorization');
            $expected = 'Bearer ' . Env::get('API_TOKEN', '');
            if ($header === '' || !hash_equals($expected, $header)) {
                throw new HttpException(401, 'Jeton Bearer manquant ou invalide.');
            }

            return $next($request);
        }

        if ($this->session->get('user_id') === null) {
            throw new HttpException(403, 'Authentification requise.');
        }

        return $next($request);
    }
}
