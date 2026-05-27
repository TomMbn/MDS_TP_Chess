<?php

class Pawn extends Piece
{
    public function __construct(PieceColor $color, Position $position)
    {
        parent::__construct($color, $position);
        $this->type = PieceType::PAWN;
    }

    protected function isValidMovementShape(Position $target): bool
    {
        $direction = $this->color === PieceColor::WHITE ? -1 : 1;
        $startRow  = $this->color === PieceColor::WHITE ? 6 : 1;

        $rowDiff = $target->getRow() - $this->position->getRow();
        $colDiff = abs($target->getColumn() - $this->position->getColumn());

        if ($rowDiff === $direction && $colDiff <= 1) {
            return true;
        }
        if ($rowDiff === 2 * $direction && $colDiff === 0 && $this->position->getRow() === $startRow) {
            return true;
        }

        return false;
    }

    // Le pion ne peut pas capturer vers l'avant, et doit capturer en diagonale
    protected function validateSpecialMovement(Board $board, Position $target): bool
    {
        $isDiagonal  = abs($target->getColumn() - $this->position->getColumn()) === 1;
        $targetPiece = $board->getPieceAt($target);

        if ($isDiagonal) {
            return $targetPiece !== null && $targetPiece->getColor() !== $this->color;
        }

        return $targetPiece === null;
    }
}
