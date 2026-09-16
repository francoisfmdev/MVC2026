<?php

declare(strict_types=1);

namespace Core;

/**
 * Classe de base des contrôleurs API.
 * Pas de Twig : uniquement json() / error(). Le même Model/DTO que le web.
 */
abstract class ApiController
{
    public function __construct(
        protected Request $request,
    ) {
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function error(string $message, int $status = 400): void
    {
        $this->json(['error' => $message, 'status' => $status], $status);
    }

    /** @return array<string, mixed> */
    protected function input(): array
    {
        return $this->request->all();
    }
}
