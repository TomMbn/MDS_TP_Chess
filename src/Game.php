<?php

class Game
{
    private Board $board;
    private PieceColor $currentPlayer;
    private PieceFactory $pieceFactory;
    private ?Position $pendingPromotion = null;

    public function __construct()
    {
        $this->board        = new Board();
        $this->currentPlayer = PieceColor::WHITE;
        $this->pieceFactory  = new PieceFactory();
    }

    public function start(): void
    {
        $this->setupPieces();
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getCurrentPlayer(): PieceColor
    {
        return $this->currentPlayer;
    }

    public function play(Move $move): void
    {
        if ($this->pendingPromotion !== null) {
            throw new InvalidMoveException("Une promotion est en attente. Appelez promote() d'abord.");
        }

        $piece = $this->board->getPieceAt($move->getFrom());

        if ($piece === null) {
            throw new NoPieceException("Aucune pièce en " . $move->getFrom()->toKey());
        }

        if ($piece->getColor() !== $this->currentPlayer) {
            throw new WrongTurnException("Ce n'est pas le tour des " . $piece->getColor()->name);
        }

        if (!$piece->canMove($this->board, $move->getTo())) {
            $targetPiece = $this->board->getPieceAt($move->getTo());
            if ($targetPiece !== null && $targetPiece->getColor() === $this->currentPlayer) {
                throw new OccupiedByAllyException("La case " . $move->getTo()->toKey() . " est occupée par un allié");
            }
            throw new InvalidMoveException("Déplacement invalide : " . $move->getFrom()->toKey() . " → " . $move->getTo()->toKey());
        }

        $this->board->movePiece($move->getFrom(), $move->getTo());

        $movedPiece   = $this->board->getPieceAt($move->getTo());
        $promotionRow = $movedPiece->getColor() === PieceColor::WHITE ? 0 : 7;
        if ($movedPiece->getType() === PieceType::PAWN && $move->getTo()->getRow() === $promotionRow) {
            $this->pendingPromotion = $move->getTo();
        } else {
            $this->switchPlayer();
        }
    }

    public function needsPromotion(): bool
    {
        return $this->pendingPromotion !== null;
    }

    public function promote(PieceType $type): void
    {
        if ($this->pendingPromotion === null) {
            return;
        }

        $pawn     = $this->board->getPieceAt($this->pendingPromotion);
        $newPiece = $this->pieceFactory->create($type, $pawn->getColor(), $this->pendingPromotion);

        $this->board->removePieceAt($this->pendingPromotion);
        $this->board->placePiece($newPiece);

        $this->pendingPromotion = null;
        $this->switchPlayer();
    }

    public function isCheck(PieceColor $color): bool
    {
        $kingPosition = $this->board->getKingPosition($color);
        if ($kingPosition === null) {
            return false;
        }

        foreach ($this->board->getPieces() as $piece) {
            if ($piece->getColor() !== $color && $piece->canMove($this->board, $kingPosition)) {
                return true;
            }
        }

        return false;
    }

    private function setupPieces(): void
    {
        $backRow = [
            PieceType::ROOK,
            PieceType::KNIGHT,
            PieceType::BISHOP,
            PieceType::QUEEN,
            PieceType::KING,
            PieceType::BISHOP,
            PieceType::KNIGHT,
            PieceType::ROOK,
        ];

        foreach ($backRow as $col => $type) {
            $this->board->placePiece($this->pieceFactory->create($type, PieceColor::BLACK, new Position(0, $col)));
            $this->board->placePiece($this->pieceFactory->create($type, PieceColor::WHITE, new Position(7, $col)));
        }

        for ($col = 0; $col < 8; $col++) {
            $this->board->placePiece($this->pieceFactory->create(PieceType::PAWN, PieceColor::BLACK, new Position(1, $col)));
            $this->board->placePiece($this->pieceFactory->create(PieceType::PAWN, PieceColor::WHITE, new Position(6, $col)));
        }
    }

    private function switchPlayer(): void
    {
        $this->currentPlayer = $this->currentPlayer->opposite();
    }
}
