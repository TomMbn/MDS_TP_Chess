<?php

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

function parseAlgebraic(string $square): Position
{
    $square = strtolower(trim($square));
    if (!preg_match('/^[a-h][1-8]$/', $square)) {
        throw new InvalidArgumentException("Case invalide : \"$square\" (attendu ex: e2)");
    }
    $col = ord($square[0]) - ord('a');
    $row = 8 - (int) $square[1];
    return new Position($row, $col);
}

$game = new Game();
$game->start();

echo "=== Jeu d'échecs interactif ===\n";
echo "Entrez un coup au format algébrique : case de départ puis case d'arrivée (ex: e2 e4).\n";
echo "Tapez 'quit' pour quitter.\n";

while (true) {
    echo $game->getBoard()->render();

    $player = $game->getCurrentPlayer();
    $label  = $player === PieceColor::WHITE ? 'Blancs' : 'Noirs';

    if ($game->isCheck($player)) {
        echo "*** ÉCHEC au roi des $label ! ***\n";
    }

    echo "$label > ";
    $line = trim(fgets(STDIN));

    if ($line === 'quit' || $line === 'exit') {
        echo "Partie terminée.\n";
        break;
    }

    $parts = preg_split('/[\s\-]+/', $line);
    if (count($parts) !== 2) {
        echo "Format invalide. Exemple : e2 e4\n";
        continue;
    }

    try {
        $from = parseAlgebraic($parts[0]);
        $to   = parseAlgebraic($parts[1]);
        $game->play(new Move($from, $to));

        if ($game->needsPromotion()) {
            $promotionMap = ['Q' => PieceType::QUEEN, 'R' => PieceType::ROOK, 'B' => PieceType::BISHOP, 'N' => PieceType::KNIGHT];
            do {
                echo "Promotion ! Choisissez [Q=Reine, R=Tour, B=Fou, N=Cavalier] : ";
                $choice = strtoupper(trim(fgets(STDIN)));
            } while (!isset($promotionMap[$choice]));
            $game->promote($promotionMap[$choice]);
        }
    } catch (NoPieceException $e) {
        echo "Erreur : " . $e->getMessage() . "\n";
    } catch (WrongTurnException $e) {
        echo "Erreur : " . $e->getMessage() . "\n";
    } catch (InvalidMoveException $e) {
        echo "Coup invalide : " . $e->getMessage() . "\n";
    } catch (OccupiedByAllyException $e) {
        echo "Erreur : " . $e->getMessage() . "\n";
    } catch (InvalidArgumentException $e) {
        echo "Erreur de saisie : " . $e->getMessage() . "\n";
    }
}
