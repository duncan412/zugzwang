<?php

declare(strict_types=1);

namespace Developful\Zugzwang\MoveGenerators;

interface MoveGeneratorInterface
{
    /**
     * Generate pseudo-legal moves (targets) for a piece on the given square.
     *
     * @param string $square
     * @param array<string, ?string> $positions board positions array (square => piece|null)
     * @param bool $white whether the piece is white
     * @return list<string> list of target squares (e.g. ['e4','f5'])
     */
    public function generate(string $square, array $positions, bool $white): array;
}
