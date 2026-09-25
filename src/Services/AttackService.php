<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Services;

use Developful\Zugzwang\MoveGenerators\KingGenerator;
use Developful\Zugzwang\MoveGenerators\KnightGenerator;
use Developful\Zugzwang\MoveGenerators\SlidingGenerator;
use Developful\Zugzwang\Utils\MoveHelpers;

final class AttackService
{
    use MoveHelpers;

    /**
     * @param array<string, ?string> $positions
     * @return bool
     */
    public function isSquareAttacked(string $square, array $positions, bool $byWhite): bool
    {
        foreach ($positions as $from => $piece) {
            if ($piece === null) {
                continue;
            }
            if ($this->isWhite($piece) !== $byWhite) {
                continue;
            }

            $type = strtolower($piece);
            $white = $this->isWhite($piece);
            /** @var list<string> $moves */
            $moves = [];

            switch ($type) {
                case 'p':
                    $dir = $white ? 1 : -1;
                    [$x, $y] = $this->squareToCoords($from);
                    foreach ([-1, 1] as $dx) {
                        $target = $this->coordsToSquare($x + $dx, $y + $dir);
                        if ($target === $square) {
                            return true;
                        }
                    }
                    break;
                case 'n':
                    $moves = (new KnightGenerator())
                        ->generate($from, $positions, $white);
                    break;
                case 'b':
                    $moves = (new SlidingGenerator([
                        [1, 1],
                        [1, -1],
                        [-1, 1],
                        [-1, -1],
                    ]))->generate($from, $positions, $white);
                    break;
                case 'r':
                    $moves = (new SlidingGenerator([
                        [1, 0],
                        [-1, 0],
                        [0, 1],
                        [0, -1],
                    ]))->generate($from, $positions, $white);
                    break;
                case 'q':
                    $moves = (new SlidingGenerator([
                        [1, 0],
                        [-1, 0],
                        [0, 1],
                        [0, -1],
                        [1, 1],
                        [1, -1],
                        [-1, 1],
                        [-1, -1],
                    ]))->generate($from, $positions, $white);
                    break;
                case 'k':
                    $moves = (new KingGenerator())
                        ->generate($from, $positions, $white);
                    break;
                default:
                    $moves = [];
            }

            if ($moves !== [] && in_array($square, $moves, true)) {
                return true;
            }
        }

        return false;
    }
}
