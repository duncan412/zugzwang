<?php

declare(strict_types=1);

namespace Developful\Zugzwang\MoveGenerators;

final class KnightGenerator implements MoveGeneratorInterface
{
    use \Developful\Zugzwang\Utils\MoveHelpers;

    /**
     * @param array<string, ?string> $positions
     * @return list<string>
     */
    public function generate(string $square, array $positions, bool $white): array
    {
        [$x, $y] = $this->squareToCoords($square);
        /** @var list<string> $moves */
        $moves = [];

        /** @var list<array{0: int, 1: int}> $deltas */
        $deltas = [[1, 2], [2, 1], [-1, 2], [-2, 1], [1, -2], [2, -1], [-1, -2], [-2, -1]];
        foreach ($deltas as [$dx, $dy]) {
            $target = $this->coordsToSquare($x + $dx, $y + $dy);
            if (!$target) {
                continue;
            }

            $piece = $positions[$target] ?? null;
            if ($piece === null || $this->isWhite($piece) !== $white) {
                $moves[] = $target;
            }
        }

        return $moves;
    }
}
