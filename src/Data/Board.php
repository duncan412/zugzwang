<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Data;

class Board
{
    /** @var array<string, ?string> */
    public array $positions;
    public string $turn;
    public string $castling;
    public string $enPassant;

    /**
     * @param array<string, ?string> $positions
     */
    public function __construct(array $positions, string $turn, string $castling, string $enPassant)
    {
        $this->positions = $positions;
        $this->turn = $turn;
        $this->castling = $castling;
        $this->enPassant = $enPassant;
    }

    /**
     * @param array<string, ?string> $board
     */
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
