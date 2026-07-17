<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Parsers;

use Developful\Zugzwang\Data\Board;

class Fen
{
    public function parse(string $fen): Board
    {
        [$board, $turn, $castling, $enPassant] = explode(' ', $fen);

        $rows = explode('/', $board);
        $position = [];

        for ($rank = 8; $rank >= 1; $rank--) {
            $file = 'a';

            foreach (str_split($rows[8 - $rank]) as $char) {
                if (is_numeric($char)) {
                    for ($i = 0; $i < (int)$char; $i++) {
                        $position[$file . $rank] = null;
                        $file++;
                    }
                } else {
                    $position[$file . $rank] = $char;
                    $file++;
                }
            }
        }

        return new Board(
            $position,
            $turn,
            $castling,
            $enPassant,
        );
    }
}
