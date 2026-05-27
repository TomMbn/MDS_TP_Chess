<?php

enum PieceColor
{
    case WHITE;
    case BLACK;

    public function opposite(): PieceColor
    {
        return $this === self::WHITE ? self::BLACK : self::WHITE;
    }
}
