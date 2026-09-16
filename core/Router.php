<?php

declare(strict_types=1);

namespace Core;

use AltoRouter;
use App\Middleware\CsrfMiddleware;
use Throwable;

/**
 * Wrapper AltoRouter.
 *
 * 1. Charge config/routes.php (tableau PHP, pas de DSL).
 * 2. Ajoute CsrfMiddleware tout seul sur les verbes mutateurs web (pas /api).
 * 3. Match URL + verbe → instancie le contrôleur via le conteneur.
 * 4. Exécute la chaîne de middlewares (onion) puis Controller#methode(Request).
 * 5. 404 : HTML ou JSON selon le préfixe /api (pas d'en-tête Accept).
 */
final class Router
{
    public function __construct(
        private Container $container,
        private View $view,
        private Request $request,
    ) {
    }

    public function dispatch(): mixed
    {
        $alto = new AltoRouter();

        /** @var list<array{0: string, 1: string, 2: string, 3?: array{middleware?: list<class-string>}}> $routes */
        $routes = require BASE_PATH . '/config/routes.php';

        foreach ($routes as $route) {
            [$method, $path, $handler] = $route;
            $options = $route[3] ?? [];
            $middlewares = $options['middleware'] ?? [];

            // CSRF obligatoire sur les formulaires HTML ; l'API s'en passe (jeton Bearer / CORS).
            if (!$this->isApiPath($path) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                array_unshift($middlewares, CsrfMiddleware::class);
            }

            $alto->map($method, $path, [
                'handler' => $handler,
                'middleware' => $middlewares,
            ]);
        }

        // On matche le chemin déjà privé de APP_BASE_PATH (voir Request::path()).
        $match = $alto->match($this->request->path(), $this->request->method());

        if ($match === false) {
            if ($this->request->method() === 'OPTIONS' && $this->request->isApi()) {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Headers: Content-Type, Authorization');
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
                http_response_code(204);

                return null;
            }

            return $this->notFound();
        }

        $this->request->setParams($match['params']);

        $target = $match['target'];
        $handler = $target['handler'];
        $middlewareClasses = $target['middleware'];

        $controllerCall = function (Request $request) use ($handler): mixed {
            return $this->callController($handler, $request);
        };

        // Construction « onion » : le dernier middleware ajouté enveloppe le précédent.
        $pipeline = $controllerCall;
        foreach (array_reverse($middlewareClasses) as $class) {
            $previous = $pipeline;
            $pipeline = function (Request $request) use ($class, $previous): mixed {
                /** @var Middleware $middleware */
                $middleware = $this->container->make($class);

                return $middleware->handle($request, $previous);
            };
        }

        return $pipeline($this->request);
    }

    private function callController(string $handler, Request $request): mixed
    {
        if (!str_contains($handler, '#')) {
            throw new \RuntimeException("Handler de route invalide : « {$handler} ». Format attendu : Namespace\\Controller#methode");
        }

        [$class, $method] = explode('#', $handler, 2);
        // Instanciation DI : le constructeur du contrôleur (View, Session…) est hydraté.
        $controller = $this->container->make($class);

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Méthode {$class}::{$method} introuvable.");
        }

        return $controller->{$method}($request);
    }

    private function isApiPath(string $path): bool
    {
        return str_starts_with($path, '/api');
    }

    private function notFound(): mixed
    {
        http_response_code(404);

        if ($this->request->isApi()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Ressource introuvable', 'status' => 404], JSON_UNESCAPED_UNICODE);

            return null;
        }

        echo $this->view->render('errors/404.twig', [
            'path' => $this->request->path(),
        ]);

        return null;
    }

    public static function handleException(Throwable $e, ?Request $request = null): void
    {
        $isApi = $request?->isApi() ?? str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), '/api');
        $debug = (string) Env::get('APP_DEBUG', 'false') === 'true';

        $status = $e instanceof HttpException ? $e->status : 500;
        http_response_code($status);

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            $payload = [
                'error' => $e->getMessage(),
                'status' => $status,
            ];
            if ($debug) {
                $payload['file'] = $e->getFile();
                $payload['line'] = $e->getLine();
            }
            echo json_encode($payload, JSON_UNESCAPED_UNICODE);

            return;
        }

        try {
            $view = new View(BASE_PATH . '/app/Web/Views', new Csrf(new Session()));
            echo $view->render('errors/500.twig', [
                'message' => $debug ? $e->getMessage() : 'Une erreur interne est survenue.',
                'trace' => $debug ? $e->getTraceAsString() : '',
            ]);
        } catch (Throwable) {
            echo '<h1>Erreur serveur</h1>';
            if ($debug) {
                echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            }
        }
    }
}
