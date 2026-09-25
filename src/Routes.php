<?php

declare(strict_types=1);

namespace Developful\Zugzwang;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\App;
use Slim\Psr7\Response;

final class Routes
{
    /**
     * @param App<ContainerInterface> $app
     */
    public static function register(App $app): void
    {
        self::jwtMiddleware($app);

        $app->get('/moves', \Developful\Zugzwang\Controllers\MoveController::class . ':list');
        $app->any('/move', \Developful\Zugzwang\Controllers\MoveController::class . ':choose');

        // Token endpoint for demonstration (would normally validate user)
        $app->post('/token', function ($request, $response) {
            $payload = [
                'sub' => 'demo-user',
                'iat' => time(),
                'exp' => time() + 3600,
            ];
            $token = JWT::encode($payload, self::jwtSecret(), 'HS256');
            $response->getBody()->write(json_encode(['token' => $token], JSON_THROW_ON_ERROR));
            return $response->withHeader('Content-Type', 'application/json');
        });
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private static function jwtMiddleware(App $app): void
    {
        $enableAuth = ($_ENV['ENABLE_JWT'] ?? '0') === '1';
        /** @var list<string> $ignorePaths */
        $ignorePaths = ['/token', '/'];

        if ($enableAuth) {
            $app->add(function (Request $request, Handler $handler) use ($ignorePaths) {
                $path = $request->getUri()->getPath();
                foreach ($ignorePaths as $ignore) {
                    if ($ignore === $path) {
                        return $handler->handle($request);
                    }
                }

                $auth = $request->getHeaderLine('Authorization');
                if (!preg_match('/Bearer\s+(\S+)/', $auth, $m)) {
                    $resp = new Response(401);
                    $resp->getBody()->write(json_encode([
                        'error' => 'unauthorized',
                        'message' => 'Missing or invalid Authorization header',
                    ], JSON_THROW_ON_ERROR));
                    return $resp->withHeader('Content-Type', 'application/json');
                }

                $token = $m[1];
                try {
                    $decoded = JWT::decode($token, new Key(self::jwtSecret(), 'HS256'));
                    return $handler->handle($request->withAttribute('jwt', $decoded));
                } catch (\Throwable $e) {
                    $resp = new Response(401);
                    $resp->getBody()->write(json_encode([
                        'error' => 'unauthorized',
                        'message' => $e->getMessage(),
                    ], JSON_THROW_ON_ERROR));
                    return $resp->withHeader('Content-Type', 'application/json');
                }
            });
        }
    }

    private static function jwtSecret(): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'change_me';
        return is_string($secret) ? $secret : 'change_me';
    }
}
