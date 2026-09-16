<?php

declare(strict_types=1);

namespace Tests\Unit;

use AltoRouter;
use PHPUnit\Framework\TestCase;

final class RoutesTest extends TestCase
{
    public function testDeclaredRoutesMatch(): void
    {
        $routes = require BASE_PATH . '/config/routes.php';
        $alto = new AltoRouter();
        foreach ($routes as $route) {
            $alto->map($route[0], $route[1], $route[2]);
        }

        $this->assertNotFalse($alto->match('/', 'GET'));
        $this->assertNotFalse($alto->match('/users', 'GET'));
        $this->assertNotFalse($alto->match('/users/12', 'GET'));
        $this->assertNotFalse($alto->match('/users/12/edit', 'GET'));
        $this->assertNotFalse($alto->match('/users', 'POST'));
        $this->assertNotFalse($alto->match('/api/users/4', 'PUT'));
        $this->assertNotFalse($alto->match('/api/users/4', 'DELETE'));
        $this->assertFalse($alto->match('/nope', 'GET'));
    }
}
