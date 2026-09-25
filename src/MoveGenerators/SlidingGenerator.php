<?php

declare(strict_types=1);

namespace Developful\Zugzwang\MoveGenerators;

final class SlidingGenerator implements MoveGeneratorInterface
{
    use \Developful\Zugzwang\Utils\MoveHelpers;

    /** @param list<array{0: int, 1: int}> $directions */
    public function __construct(private array $directions) {}

    /**
     * @param array<string, ?string> $positions
     * @return list<string>
     */
    public function generate(string $square, array $positions, bool $white): array
    {
        [$x, $y] = $this->squareToCoords($square);
        /** @var list<string> $moves */
        $moves = [];

        foreach ($this->directions as [$dx, $dy]) {
            $cx = $x + $dx;
            $cy = $y + $dy;
            while (($target = $this->coordsToSquare($cx, $cy)) !== null) {
                $piece = $positions[$target] ?? null;
                if ($piece === null) {
                    $moves[] = $target;
                } else {
                    if ($this->isWhite($piece) !== $white) {
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
}
