<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Services;

use Developful\Zugzwang\Data\Board;
use Developful\Zugzwang\MoveGenerators\KingGenerator;
use Developful\Zugzwang\MoveGenerators\KnightGenerator;
use Developful\Zugzwang\MoveGenerators\PawnGenerator;
use Developful\Zugzwang\MoveGenerators\SlidingGenerator;
use Developful\Zugzwang\Parsers\Fen;
use Developful\Zugzwang\Utils\MoveHelpers;

final class ChessService
{
    use MoveHelpers;

    public function __construct(
        private readonly Fen $parser,
        private AttackService $attackService
    ) {}

    /**
     * @return list<string>
     */
    public function listMoves(string $fen): array
    {
        /** @var Board */
        $board = $this->parser->parse($fen);
        /** @var list<string> $moves */
        $moves = [];

        $sideWhite = strtolower($board->turn) === 'w';

        foreach ($board->positions as $square => $piece) {
            if ($piece === null) {
                continue;
            }
            if ($this->isWhite($piece) !== $sideWhite) {
                continue;
            }

            $white = $this->isWhite($piece);

            $targets = match (strtolower($piece)) {
                'p' => (new PawnGenerator())
                    ->generate($square, $board->positions, $white),
                'n' => (new KnightGenerator())
                    ->generate($square, $board->positions, $white),
                'r' => (new SlidingGenerator([
                    [1, 0],
                    [-1, 0],
                    [0, 1],
                    [0, -1],
                ]))->generate($square, $board->positions, $white),
                'b' => (new SlidingGenerator([
                    [1, 1],
                    [1, -1],
                    [-1, 1],
                    [-1, -1],
                ]))->generate($square, $board->positions, $white),
                'q' => (new SlidingGenerator([
                    [1, 0],
                    [-1, 0],
                    [0, 1],
                    [0, -1],
                    [1, 1],
                    [1, -1],
                    [-1, 1],
                    [-1, -1],
                ]))->generate($square, $board->positions, $white),
                'k' => (new KingGenerator($board->castling))
                    ->generate($square, $board->positions, $white),
                default => [],
            };

            foreach ($targets as $to) {
                if ($this->isLegalMove($board, $square, $to, $white)) {
                    $moves[] = $square . $to;
                }
            }
        }

        return $moves;
    }

    public function isLegalMove(Board $board, string $from, string $to, bool $white): bool
    {
        /** @var array<string, ?string> $newBoard */
        $newBoard = $board->positions;
        $newBoard[$to] = $newBoard[$from] ?? null;
        $newBoard[$from] = null;

        $kingSquare = $board->findKing($newBoard, $white);
        if ($kingSquare === null) {
            return false;
        }

        return !$this->attackService->isSquareAttacked($kingSquare, $newBoard, !$white);
    }

    public function chooseMove(string $fen): ?string
    {
        $moves = $this->listMoves($fen);
        if (empty($moves)) {
            return null;
        }

        return $moves[array_rand($moves)];
    }
}
