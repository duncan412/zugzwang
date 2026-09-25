<?php

declare(strict_types=1);

namespace Developful\Zugzwang\Transformers;

use League\Fractal\TransformerAbstract;

final class MoveTransformer extends TransformerAbstract
{
    /**
     * @return array{move: string, from: string, to: string}
     */
    public function transform(string $move): array
    {
        return [
            'move' => $move,
            'from' => substr($move, 0, 2),
            'to' => substr($move, 2, 2),
        ];
    }
}
