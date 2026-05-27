<?php

// Chargement des dépendances dans l'ordre d'héritage
require_once __DIR__ . '/src/Contract/Renderable.php';
require_once __DIR__ . '/src/Exception/ChessException.php';
require_once __DIR__ . '/src/Exception/InvalidMoveException.php';
require_once __DIR__ . '/src/Exception/NoPieceException.php';
require_once __DIR__ . '/src/Exception/WrongTurnException.php';
require_once __DIR__ . '/src/Exception/OccupiedByAllyException.php';
require_once __DIR__ . '/src/Enum/PieceColor.php';
require_once __DIR__ . '/src/Enum/PieceType.php';
require_once __DIR__ . '/src/Position.php';
require_once __DIR__ . '/src/Move.php';
require_once __DIR__ . '/src/Board.php';
require_once __DIR__ . '/src/Piece/Piece.php';
require_once __DIR__ . '/src/Piece/King.php';
require_once __DIR__ . '/src/Piece/Queen.php';
require_once __DIR__ . '/src/Piece/Rook.php';
require_once __DIR__ . '/src/Piece/Bishop.php';
require_once __DIR__ . '/src/Piece/Knight.php';
require_once __DIR__ . '/src/Piece/Pawn.php';
require_once __DIR__ . '/src/Factory/PieceFactory.php';
require_once __DIR__ . '/src/Game.php';

$game = new Game();
$game->start();

echo "=== Plateau initial ===";
echo $game->getBoard()->render();

$moves = [
    ['from' => '6:4', 'to' => '4:4', 'desc' => 'e2-e4 (pion blanc)'],
    ['from' => '1:4', 'to' => '3:4', 'desc' => 'e7-e5 (pion noir)'],
    ['from' => '7:6', 'to' => '5:5', 'desc' => 'g1-f3 (cavalier blanc)'],
    ['from' => '0:1', 'to' => '2:2', 'desc' => 'b8-c6 (cavalier noir)'],
    ['from' => '7:5', 'to' => '4:2', 'desc' => 'f1-c4 (fou blanc)'],
];

foreach ($moves as $m) {
    try {
        $from = Position::fromKey($m['from']);
        $to   = Position::fromKey($m['to']);
        $game->play(new Move($from, $to));
        echo "✓ {$m['desc']}\n";
    } catch (ChessException $e) {
        echo "✗ {$m['desc']} → " . $e->getMessage() . "\n";
    }
}

echo "\n=== Plateau après les coups ===";
echo $game->getBoard()->render();

// Démonstration des exceptions
echo "=== Tests d'erreurs ===\n";

// Après 5 coups (B B B B B), c'est le tour des noirs
$errorCases = [
    ['from' => '5:5', 'to' => '3:4', 'desc' => 'Jouer un blanc quand c\'est le tour des noirs → WrongTurnException'],
    ['from' => '2:5', 'to' => '3:5', 'desc' => 'Case vide → NoPieceException'],
    ['from' => '3:4', 'to' => '5:4', 'desc' => 'Pion noir en sens inverse → InvalidMoveException'],
    ['from' => '2:2', 'to' => '1:0', 'desc' => 'Cavalier noir sur pion allié → OccupiedByAllyException'],
];

foreach ($errorCases as $m) {
    try {
        $from = Position::fromKey($m['from']);
        $to   = Position::fromKey($m['to']);
        $game->play(new Move($from, $to));
        echo "  (aucune erreur)\n";
    } catch (NoPieceException $e) {
        echo "  NoPieceException : " . $e->getMessage() . "\n";
    } catch (WrongTurnException $e) {
        echo "  WrongTurnException : " . $e->getMessage() . "\n";
    } catch (InvalidMoveException $e) {
        echo "  InvalidMoveException : " . $e->getMessage() . "\n";
    } catch (OccupiedByAllyException $e) {
        echo "  OccupiedByAllyException : " . $e->getMessage() . "\n";
    }
}

echo "\nBlancs en échec : " . ($game->isCheck(PieceColor::WHITE) ? 'oui' : 'non') . "\n";
echo "Noirs en échec  : " . ($game->isCheck(PieceColor::BLACK) ? 'oui' : 'non') . "\n";
