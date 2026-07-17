<?php

declare(strict_types=1);

namespace Developful\Zugzwang;

use Developful\Zugzwang\Data\Board;
use Developful\Zugzwang\Parsers\Fen;

class Chess
{
    public function getPossibleMoves(string $fen)
    {
        $parser = new Fen;
        /** @var Board */
        $board = $parser->parse($fen);

        $moves = [];

        foreach ($board->positions as $square => $piece) {
            if (!$piece) continue;

            if ($this->isWhite($piece) !== $this->isWhite($board->turn))     continue;

            $white = $this->isWhite($piece);

            switch ($this->pieceType($piece)) {
                case 'p':
                    $targets = $this->pawnMoves($square, $board->positions, $white);
                    break;
                case 'r':
                    $targets = $this->slidingMoves($square, $board->positions, $white, [
                        [1, 0],
                        [-1, 0],
                        [0, 1],
                        [0, -1]
                    ]);
                    break;
                case 'b':
                    $targets = $this->slidingMoves($square, $board->positions, $white, [[1, 1], [1, -1], [-1, 1], [-1, -1]]);
                    break;
                case 'q':
                    $targets = $this->slidingMoves($square, $board->positions, $white, [[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [1, -1], [-1, 1], [-1, -1]]);
                    break;
                case 'n':
                    $targets = $this->knightMoves($square, $board->positions, $white);
                    break;
                case 'k':
                    $targets = $this->kingMoves($square, $board->positions, $white, $board->castling);
                    break;
                default:
                    $targets = [];
            }

            foreach ($targets as $to) {
                if ($this->isLegalMove($board, $square, $to, $white)) {
                    $moves[] = $square . $to;
                }
            }
        }

        return $moves;
    }

    function isLegalMove(Board $board, string $from, string $to, bool $white): bool
    {
        $newBoard = $board->positions;

        $newBoard[$to] = $newBoard[$from];
        $newBoard[$from] = null;

        $kingSquare = $board->findKing($newBoard, $white);

        return !$this->isSquareAttacked($kingSquare, $newBoard, !$white);
    }


    public function squareToCoords(string $square): array
    {
        return [
            ord($square[0]) - 97,
            (int)$square[1] - 1
        ];
    }

    public function coordsToSquare(int $x, int $y): ?string
    {
        if ($x < 0 || $x > 7 || $y < 0 || $y > 7) return null;
        return chr(97 + $x) . ($y + 1);
    }

    public function isWhite(string $piece): bool
    {
        return ctype_upper($piece);
    }

    public function pieceType(string $piece): string
    {
        return strtolower($piece);
    }

    public function knightMoves(string $square, array $board, bool $white): array
    {
        [$x, $y] = $this->squareToCoords($square);
        $moves = [];

        $deltas = [
            [1, 2],
            [2, 1],
            [-1, 2],
            [-2, 1],
            [1, -2],
            [2, -1],
            [-1, -2],
            [-2, -1],
        ];

        foreach ($deltas as [$dx, $dy]) {
            $target = $this->coordsToSquare($x + $dx, $y + $dy);

            if (!$target) continue; // off-board
            if (empty($board[$target]) || $this->isWhite($board[$target]) !== $white) {
                $moves[] = $target;
            }
        }

        return $moves;
    }

    function kingMoves(string $square, array $board, bool $white, string $castling): array
    {
        [$x, $y] = $this->squareToCoords($square);
        $moves = [];

        // Normal one-square moves
        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                if ($dx === 0 && $dy === 0) continue;

                $target = $this->coordsToSquare($x + $dx, $y + $dy);
                if (!$target) continue;

                if (empty($board[$target]) || $this->isWhite($board[$target]) !== $white) {
                    $moves[] = $target;
                }
            }
        }

        // Castling
        if ($white && $square === 'e1') {
            // Kingside
            if (
                str_contains($castling, 'K') &&
                empty($board['f1']) && empty($board['g1'])
            ) {
                $moves[] = 'g1';
            }
            // Queenside
            if (
                str_contains($castling, 'Q') &&
                empty($board['d1']) && empty($board['c1']) && empty($board['b1'])
            ) {
                $moves[] = 'c1';
            }
        }

        if (!$white && $square === 'e8') {
            // Kingside
            if (
                str_contains($castling, 'k') &&
                empty($board['f8']) && empty($board['g8'])
            ) {
                $moves[] = 'g8';
            }
            // Queenside
            if (
                str_contains($castling, 'q') &&
                empty($board['d8']) && empty($board['c8']) && empty($board['b8'])
            ) {
                $moves[] = 'c8';
            }
        }

        return $moves;
    }


    public function slidingMoves(string $square, array $board, bool $white, array $directions): array
    {
        [$x, $y] = $this->squareToCoords($square);
        $moves = [];

        foreach ($directions as [$dx, $dy]) {
            $cx = $x + $dx;
            $cy = $y + $dy;

            while ($target = $this->coordsToSquare($cx, $cy)) {
                if (empty($board[$target])) {
                    $moves[] = $target;
                } else {
                    if ($this->isWhite($board[$target]) !== $white) {
                        $moves[] = $target;
                    }
                    break;
                }

                $cx += $dx;
                $cy += $dy;
            }
        }

        return $moves;
    }

    function pawnMoves(string $square, array $board, bool $white): array
    {
        [$x, $y] = $this->squareToCoords($square);
        $moves = [];

        $dir = $white ? 1 : -1;
        $startRank = $white ? 1 : 6;

        // Forward
        $forward = $this->coordsToSquare($x, $y + $dir);
        if ($forward && empty($board[$forward])) {
            $moves[] = $forward;

            // Double move
            if ($y === $startRank) {
                $double = $this->coordsToSquare($x, $y + 2 * $dir);
                if ($double && empty($board[$double])) {
                    $moves[] = $double;
                }
            }
        }

        // Captures
        foreach ([-1, 1] as $dx) {
            $target = $this->coordsToSquare($x + $dx, $y + $dir);
            if ($target && !empty($board[$target])) {
                if ($this->isWhite($board[$target]) !== $white) {
                    $moves[] = $target;
                }
            }
        }

        return $moves;
    }

    public function isSquareAttacked(string $square, array $board, bool $byWhite): bool
    {
        foreach ($board as $from => $piece) {
            if (!$piece) continue;
            if ($this->isWhite($piece) !== $byWhite) continue;

            $white = $this->isWhite($piece);
            $type = $this->pieceType($piece);

            switch ($type) {
                case 'p':
                    $dir = $white ? 1 : -1;
                    [$x, $y] = $this->squareToCoords($from);
                    foreach ([-1, 1] as $dx) {
                        $target = $this->coordsToSquare($x + $dx, $y + $dir);
                        if ($target === $square) return true;
                    }
                    break;

                case 'r':
                    $moves = $this->slidingMoves($from, $board, $white, [
                        [1, 0],
                        [-1, 0],
                        [0, 1],
                        [0, -1]
                    ]);
                    break;

                case 'b':
                    $moves = $this->slidingMoves($from, $board, $white, [
                        [1, 1],
                        [1, -1],
                        [-1, 1],
                        [-1, -1]
                    ]);
                    break;

                case 'q':
                    $moves = $this->slidingMoves($from, $board, $white, [
                        [1, 0],
                        [-1, 0],
                        [0, 1],
                        [0, -1],
                        [1, 1],
                        [1, -1],
                        [-1, 1],
                        [-1, -1]
                    ]);
                    break;

                case 'n':
                    $moves = $this->knightMoves($from, $board, $white);
                    break;

                case 'k':
                    $moves = $this->kingMoves($from, $board, $white, '');
                    break;

                default:
                    $moves = [];
            }

            if (in_array($square, $moves ?? [], true)) {
                return true;
            }
        }

        return false;
    }
}
