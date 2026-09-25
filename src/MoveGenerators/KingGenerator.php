<?php

declare(strict_types=1);

namespace Developful\Zugzwang\MoveGenerators;

final class KingGenerator implements MoveGeneratorInterface
{
    use \Developful\Zugzwang\Utils\MoveHelpers;

    public function __construct(public string $castling = '') {}

    /**
     * @param array<string, ?string> $positions
     * @return list<string>
     */
    public function generate(string $square, array $positions, bool $white): array
    {
        [$x, $y] = $this->squareToCoords($square);
        /** @var list<string> $moves */
        $moves = [];

        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                if ($dx === 0 && $dy === 0) {
                    continue;
                }

                $target = $this->coordsToSquare($x + $dx, $y + $dy);
                if ($target === null) {
                    continue;
                }

                $piece = $positions[$target] ?? null;
                if ($piece === null || $this->isWhite($piece) !== $white) {
                    $moves[] = $target;
                }
            }
        }

        // Castling simplified: only check empty squares and castling flag
        if ($white && $square === 'e1') {
            if (
                str_contains($this->castling, 'K') &&
                empty($positions['f1']) && empty($positions['g1'])
            ) {
                $moves[] = 'g1';
            }
            if (
                str_contains($this->castling, 'Q') &&
                empty($positions['d1']) && empty($positions['c1']) && empty($positions['b1'])
            ) {
                $moves[] = 'c1';
            }
        }
        if (!$white && $square === 'e8') {
            if (
                str_contains($this->castling, 'k') &&
                empty($positions['f8']) && empty($positions['g8'])
            ) {
                $moves[] = 'g8';
            }
            if (
                str_contains($this->castling, 'q') &&
                empty($positions['d8']) && empty($positions['c8']) && empty($positions['b8'])
            ) {
                $moves[] = 'c8';
            }
        }

        return $moves;
    }
}
