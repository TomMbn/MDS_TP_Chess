<?php

abstract class Piece implements Renderable
{
    protected PieceColor $color;
    protected Position $position;
    protected PieceType $type;

    private const SYMBOLS = [
        'KING'   => ['K', 'k'],
        'QUEEN'  => ['Q', 'q'],
        'ROOK'   => ['R', 'r'],
        'BISHOP' => ['B', 'b'],
        'KNIGHT' => ['N', 'n'],
        'PAWN'   => ['P', 'p'],
    ];

    public function __construct(PieceColor $color, Position $position)
    {
        $this->color = $color;
        $this->position = $position;
    }

    public function getColor(): PieceColor
    {
        return $this->color;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }

    public function getType(): PieceType
    {
        return $this->type;
    }

    public function render(): string
    {
        [$white, $black] = self::SYMBOLS[$this->type->name];
        return $this->color === PieceColor::WHITE ? $white : $black;
    }

    public function canMove(Board $board, Position $target): bool
    {
        if ($this->position->equals($target)) {
            return false;
        }
        if (!$this->isValidMovementShape($target)) {
            return false;
        }
        if (!$this->canJump() && !$board->isPathClear($this->position, $target)) {
            return false;
        }
        if (!$this->validateSpecialMovement($board, $target)) {
            return false;
        }
        return true;
    }

    abstract protected function isValidMovementShape(Position $target): bool;

    protected function canCapture(Board $board, Position $target): bool
    {
        $piece = $board->getPieceAt($target);
        return $piece === null || $piece->getColor() !== $this->color;
    }

    // Surcharge dans Knight
    protected function canJump(): bool
    {
        return false;
    }

    // Surcharge dans Pawn
    protected function validateSpecialMovement(Board $board, Position $target): bool
    {
        return true;
    }
}
