<?php

declare(strict_types=1);

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Wrapper Twig. Templates uniquement dans app/Web/Views/.
 *
 * Fonctions globales enregistrées : asset(), csrf_field(), url().
 * Le flash session est injecté à chaque render() (consommé à la lecture).
 */
final class View
{
    private Environment $twig;

    public function __construct(
        private string $viewsPath,
        private Csrf $csrf,
    ) {
        $loader = new FilesystemLoader($this->viewsPath);
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => (string) Env::get('APP_DEBUG', 'false') === 'true',
            'strict_variables' => false,
        ]);

        $this->twig->addFunction(new TwigFunction('asset', [$this, 'asset']));
        $this->twig->addFunction(new TwigFunction('csrf_field', [$this->csrf, 'field'], ['is_safe' => ['html']]));
        $this->twig->addFunction(new TwigFunction('url', [$this, 'url']));
    }

    /**
     * URL d'un fichier dans public/assets/, avec cache-busting (filemtime).
     */
    public function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = BASE_PATH . '/public/assets/' . $path;
        $version = is_file($file) ? (string) filemtime($file) : (string) time();

        return $this->url('/assets/' . $path) . '?v=' . $version;
    }

    public function url(string $path): string
    {
        $base = rtrim((string) Env::get('APP_BASE_PATH', ''), '/');
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $base . $path;
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $data['flash_success'] = $data['flash_success'] ?? (new Session())->getFlash('success');
        $data['flash_error'] = $data['flash_error'] ?? (new Session())->getFlash('error');
        $data['flash_errors'] = $data['flash_errors'] ?? (new Session())->getFlash('errors');
        $data['old'] = $data['old'] ?? (new Session())->getFlash('old');

        return $this->twig->render($template, $data);
    }
}
