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
        $light = "\033[48;5;222m"; // case claire (beige doré)
        $dark  = "\033[48;5;130m"; // case sombre (brun)
        $wFg   = "\033[1;97m";     // pièce blanche : blanc gras
        $bFg   = "\033[1;30m";     // pièce noire   : noir gras
        $reset = "\033[0m";
        $dim   = "\033[2;37m";     // labels discrets

        $header  = "{$dim}     a  b  c  d  e  f  g  h{$reset}";
        $output  = "\n{$header}\n";

        for ($row = 0; $row < 8; $row++) {
            $rank    = 8 - $row;
            $output .= "{$dim} {$rank}  {$reset}";
            for ($col = 0; $col < 8; $col++) {
                $bg    = (($row + $col) % 2 === 0) ? $light : $dark;
                $piece = $this->getPieceAt(new Position($row, $col));
                if ($piece !== null) {
                    $fg     = $piece->getColor() === PieceColor::WHITE ? $wFg : $bFg;
                    $sym    = $piece->render();
                    $output .= "{$bg}{$fg} {$sym} {$reset}";
                } else {
                    $output .= "{$bg}   {$reset}";
                }
            }
            $output .= "  {$dim}{$rank}{$reset}\n";
        }

        $output .= "{$header}\n";
        return $output;
    }

    private function sign(int $n): int
    {
        return ($n > 0) - ($n < 0);
    }
}
