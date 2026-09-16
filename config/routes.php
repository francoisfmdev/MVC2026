<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use App\Middleware\LoggingMiddleware;

/**
 * Table des routes (web + API dans le même fichier, séparées par le préfixe /api).
 *
 * Format d'une ligne :
 *   [ verbe HTTP, chemin AltoRouter, 'Namespace\\Controller#methode', ['middleware' => [...]] ]
 *
 * [i:id] = entier nommé « id » → Request::param('id').
 * CSRF : ajouté tout seul sur les POST web (voir Core\Router).
 *
 * @return list<array{0: string, 1: string, 2: string, 3?: array{middleware?: list<class-string>}}>
 */
return [
    ['GET', '/', 'App\\Web\\Controllers\\HomeController#index', ['middleware' => [LoggingMiddleware::class]]],

    ['GET', '/users', 'App\\Web\\Controllers\\UserController#index', ['middleware' => [LoggingMiddleware::class]]],
    ['GET', '/users/create', 'App\\Web\\Controllers\\UserController#create'],
    ['POST', '/users', 'App\\Web\\Controllers\\UserController#store'],
    ['GET', '/users/[i:id]', 'App\\Web\\Controllers\\UserController#show'],
    ['GET', '/users/[i:id]/edit', 'App\\Web\\Controllers\\UserController#edit'],
    ['POST', '/users/[i:id]', 'App\\Web\\Controllers\\UserController#update'],
    ['POST', '/users/[i:id]/delete', 'App\\Web\\Controllers\\UserController#destroy'],

    ['GET', '/api/users', 'App\\Api\\Controllers\\UserController#index', ['middleware' => [CorsMiddleware::class, LoggingMiddleware::class]]],
    ['GET', '/api/users/[i:id]', 'App\\Api\\Controllers\\UserController#show', ['middleware' => [CorsMiddleware::class]]],
    ['POST', '/api/users', 'App\\Api\\Controllers\\UserController#store', ['middleware' => [CorsMiddleware::class]]],
    ['PUT', '/api/users/[i:id]', 'App\\Api\\Controllers\\UserController#update', ['middleware' => [CorsMiddleware::class]]],
    ['PATCH', '/api/users/[i:id]', 'App\\Api\\Controllers\\UserController#update', ['middleware' => [CorsMiddleware::class]]],
    ['DELETE', '/api/users/[i:id]', 'App\\Api\\Controllers\\UserController#destroy', ['middleware' => [CorsMiddleware::class]]],
];
