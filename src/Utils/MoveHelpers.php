<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Utils;

trait MoveHelpers
{
    /**
     * @return array{0: int, 1: int}
     */
    protected function squareToCoords(string $square): array
    {
        return [ord($square[0]) - 97, (int) $square[1] - 1];
    }

    protected function coordsToSquare(int $x, int $y): ?string
    {
        if ($x < 0 || $x > 7 || $y < 0 || $y > 7) {
            return null;
        }

        return chr(97 + $x) . ($y + 1);
    }

    protected function isWhite(string $piece): bool
    {
        return ctype_upper($piece);
    }
}
