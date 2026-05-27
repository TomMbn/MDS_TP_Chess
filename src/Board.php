<?php

class Board implements Renderable
{
    private array $pieces = [];

    public function placePiece(Piece $piece): void
    {
        $this->pieces[$piece->getPosition()->toKey()] = $piece;
    }

    public function getPieceAt(Position $position): ?Piece
    {
        return $this->pieces[$position->toKey()] ?? null;
    }

    public function hasPieceAt(Position $position): bool
    {
        return isset($this->pieces[$position->toKey()]);
    }

    public function removePieceAt(Position $position): void
    {
        unset($this->pieces[$position->toKey()]);
    }

    public function movePiece(Position $from, Position $to): void
    {
        $piece = $this->getPieceAt($from);
        $this->removePieceAt($from);
        $piece->setPosition($to);
        $this->placePiece($piece);
    }

    public function isPathClear(Position $from, Position $to): bool
    {
        $rowStep = $this->sign($to->getRow() - $from->getRow());
        $colStep = $this->sign($to->getColumn() - $from->getColumn());

        $row = $from->getRow() + $rowStep;
        $col = $from->getColumn() + $colStep;

        while ($row !== $to->getRow() || $col !== $to->getColumn()) {
            if ($this->hasPieceAt(new Position($row, $col))) {
                return false;
            }
            $row += $rowStep;
            $col += $colStep;
        }

        return true;
    }

    public function getPieces(): array
    {
        return array_values($this->pieces);
    }

    public function getKingPosition(PieceColor $color): ?Position
    {
        foreach ($this->pieces as $piece) {
            if ($piece->getColor() === $color && $piece->getType() === PieceType::KING) {
                return $piece->getPosition();
            }
        }
        return null;
    }

    public function render(): string
    {
        $output = "\n  a b c d e f g h\n";
        for ($row = 0; $row < 8; $row++) {
            $output .= (8 - $row) . ' ';
            for ($col = 0; $col < 8; $col++) {
                $piece   = $this->getPieceAt(new Position($row, $col));
                $output .= ($piece !== null ? $piece->render() : '.') . ' ';
            }
            $output .= (8 - $row) . "\n";
        }
        $output .= "  a b c d e f g h\n";
        return $output;
    }

    private function sign(int $n): int
    {
        return ($n > 0) - ($n < 0);
    }
}
