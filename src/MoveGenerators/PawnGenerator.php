<?php

declare(strict_types=1);

namespace Developful\Zugzwang\MoveGenerators;

final class PawnGenerator implements MoveGeneratorInterface
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

        $dir = $white ? 1 : -1;
        $startRank = $white ? 1 : 6;

        $forward = $this->coordsToSquare($x, $y + $dir);
        if ($forward !== null && empty($positions[$forward])) {
            $moves[] = $forward;
            if ($y === $startRank) {
                $double = $this->coordsToSquare($x, $y + 2 * $dir);
                if ($double !== null && empty($positions[$double])) {
                    $moves[] = $double;
                }
            }
        }

        foreach ([-1, 1] as $dx) {
            $target = $this->coordsToSquare($x + $dx, $y + $dir);
            $piece = $target !== null ? ($positions[$target] ?? null) : null;
            if ($target !== null && $piece !== null && $this->isWhite($piece) !== $white) {
                $moves[] = $target;
            }
        }

        return $moves;
    }
}
