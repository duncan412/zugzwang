<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Data;

class Board
{
    public array $positions;
    public string $turn;
    public string $castling;
    public string $enPassant;

    public function __construct(array $positions, string $turn, string $castling, string $enPassant)
    {
        $this->positions = $positions;
        $this->turn = $turn;
        $this->castling = $castling;
        $this->enPassant = $enPassant;
    }

    public function findKing(array $board, bool $white): ?string
    {
        foreach ($board as $square => $piece) {
            if ($piece === ($white ? 'K' : 'k')) {
                return $square;
            }
        }
        return null;
    }
}
