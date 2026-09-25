<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Controllers;

use Developful\Zugzwang\Services\ChessService;
use Developful\Zugzwang\Services\TransformerService;
use Developful\Zugzwang\Transformers\MoveTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class MoveController
{
    private ChessService $service;
    private TransformerService $transformer;

    // Services injected by PHP-DI
    public function __construct(ChessService $service, TransformerService $transformer)
    {
        $this->service = $service;
        $this->transformer = $transformer;
    }

    public function list(Request $request, Response $response): Response
    {
        /** @var array<string, mixed> $params */
        $params = (array) $request->getParsedBody();
        $fen = $params['fen'] ?? $request->getQueryParams()['fen'] ?? '';
        $moves = $this->service->listMoves($fen);
        $out = $this->transformer->transformCollectionJson($moves, new MoveTransformer());
        $response->getBody()->write($out['body']);
        return $response->withHeader('Content-Type', $out['contentType']);
    }

    public function choose(Request $request, Response $response): Response
    {
        /** @var array<string, mixed> $params */
        $params = (array) $request->getParsedBody();
        $fen = $params['fen'] ?? $request->getQueryParams()['fen'] ?? '';

        if (empty($fen)) {
            $response->getBody()->write(json_encode(['error' => 'Missing FEN parameter'], JSON_THROW_ON_ERROR));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $move = $this->service->chooseMove($fen);
        $out = $this->transformer->transformCollectionJson($move ? [$move] : [], new MoveTransformer());
        $response->getBody()->write($out['body']);
        return $response->withHeader('Content-Type', $out['contentType']);
    }
}
