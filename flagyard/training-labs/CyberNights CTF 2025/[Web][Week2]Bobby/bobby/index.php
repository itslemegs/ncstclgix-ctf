<?php
require_once('classes.php');
session_start();

if (!isset($_SESSION['game_state'])) {
    $_SESSION['game_state'] = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
}

if (isset($_POST['move'])) {
    $move = $_POST['move'];
    if (preg_match('/^[KQRBNP]?[a-h]?[1-8]?x?[a-h][1-8](?:=[QRBN])?[+#]?$/', $move)) {
        $_SESSION['moves'][] = $move;
    }
}

if (isset($_POST['save_position'])) {
    $savedGame = [
        'fen' => $_POST['position'],
        'pgn' => isset($_POST['pgn']) ? $_POST['pgn'] : '',
        'timestamp' => time()
    ];
    $savedPosition = base64_encode(serialize($savedGame));
    setcookie('saved_position', $savedPosition, time() + 86400, '/');
}

if (isset($_COOKIE['saved_position'])) {
    try {
        $obj = unserialize(base64_decode($_COOKIE['saved_position']));
    } catch (Exception $e) {
        $error = 'Invalid position data!';
    }
}

if (isset($_POST['update_position'])) {
    $_SESSION['game_state'] = $_POST['fen'];
    if (isset($_POST['pgn'])) {
        $_SESSION['moves'] = explode(',', $_POST['pgn']);
    }
    exit;
}

if (isset($_POST['analyze'])) {
    $analysis = [
        'evaluation' => 0.0,
        'best_move' => '',
        'position_type' => '',
        'comments' => []
    ];
    
    if (isset($_SESSION['game_state'])) {
        $fen = $_SESSION['game_state'];
        preg_match_all('/[PNBRQK]/i', $fen, $matches);
        $pieces = $matches[0];
        foreach ($pieces as $piece) {
            switch (strtoupper($piece)) {
                case 'P': $analysis['evaluation'] += ($piece === 'P' ? 1 : -1); break;
                case 'N': 
                case 'B': $analysis['evaluation'] += ($piece === strtoupper($piece) ? 3 : -3); break;
                case 'R': $analysis['evaluation'] += ($piece === 'R' ? 5 : -5); break;
                case 'Q': $analysis['evaluation'] += ($piece === 'Q' ? 9 : -9); break;
            }
        }
        
        $analysis['comments'] = [
            "Position reminds me of my game against Petrosian, 1971",
            "The center control is crucial here",
            "Interesting pawn structure",
            "This position requires precise calculation",
            "The knight outpost on e5 is particularly strong",
            "Pawn structure reminds me of the Sicilian Dragon",
            "Black's dark squares are weakened",
            "White has pressure along the e-file"
        ];

        $analysis['position_type'] = [
            "Open position",
            "Complex tactical position",
            "Requires strategic planning"
        ];

        $analysis['best_move'] = [
            "Consider Nf3 to control the center",
            "Look for tactical opportunities with e4",
            "The bishop pair gives long-term advantage"
        ];
    }
    
    $_SESSION['analysis'] = $analysis;
}

function logError($message, $context = []) {
    error_log(sprintf(
        "[Chess Analysis Error] %s | Context: %s",
        $message,
        json_encode($context)
    ));
}

try {
} catch (Exception $e) {
    logError($e->getMessage(), [
        'position' => $_SESSION['game_state'] ?? 'unknown',
        'user_action' => $_POST['action'] ?? 'unknown'
    ]);
    $error = 'An error occurred during analysis';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bobby's Chess Analysis Tool</title>
    <link rel="stylesheet" href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css">
    <style>
        body { 
            font-family: 'Georgia', serif;
            background: #1a1a1a;
            color: #d4d4d4;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px;
        }
        .title {
            grid-column: 1 / -1;
            text-align: center;
            border-bottom: 2px solid #gold;
            padding-bottom: 20px;
        }
        .title h1 {
            font-size: 2.5em;
            color: #d4af37;
            margin: 0;
        }
        .title p {
            color: #8b8b8b;
            font-style: italic;
        }
        .board-container { 
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        #board { 
            width: 600px;
            margin: 0 auto;
            border: 8px solid #463714;
            border-radius: 4px;
        }
        .controls { 
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #3a3a3a;
        }
        .move-list { 
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
        }
        .move-list h3 {
            color: #d4af37;
            margin-top: 0;
            border-bottom: 1px solid #3a3a3a;
            padding-bottom: 10px;
        }
        .analysis { 
            grid-column: 1 / -1;
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
        }
        .analysis h3 {
            color: #d4af37;
            margin-top: 0;
        }
        button { 
            background: #463714;
            color: #d4af37;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Georgia', serif;
            transition: all 0.3s ease;
        }
        button:hover {
            background: #d4af37;
            color: #463714;
        }
        #pgn {
            font-family: 'Courier New', monospace;
            line-height: 1.6;
        }
        .quote {
            grid-column: 1 / -1;
            text-align: center;
            font-style: italic;
            color: #8b8b8b;
            margin: 20px 0;
        }
        .analysis-content {
            color: #d4d4d4;
            font-family: 'Courier New', monospace;
        }
        .evaluation {
            font-size: 1.2em;
            margin: 10px 0;
            padding: 10px;
            background: #1a1a1a;
            border-radius: 4px;
        }
        .comments {
            margin-top: 20px;
        }
        .comments ul {
            list-style: none;
            padding-left: 0;
        }
        .comments li {
            padding: 8px 0;
            border-bottom: 1px solid #3a3a3a;
            color: #8b8b8b;
            font-style: italic;
        }
        .comments li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            <h1>Bobby Fischer's Immortal Analysis</h1>
            <p>"I just made the moves I thought were best. I was just lucky." - Bobby Fischer</p>
        </div>
        
        <div class="board-container">
            <div id="board"></div>
            <div class="controls">
                <button id="flipBtn">Flip Board</button>
                <button id="undoBtn">Undo Move</button>
                <button type="submit" name="save_position">Save Position</button>
                <button type="submit" name="analyze">Analyze Position</button>
            </div>
        </div>

        <div class="move-list">
            <h3>Move History</h3>
            <div id="pgn"></div>
        </div>

        <div class="quote">
            "Every chess master was once a beginner." - Irving Chernev
        </div>

        <div class="analysis">
            <h3>Position Analysis</h3>
            <div class="analysis-content">
                <?php if (isset($_SESSION['analysis'])): ?>
                    <div class="evaluation">
                        <strong>Evaluation:</strong> 
                        <?php 
                        $eval = $_SESSION['analysis']['evaluation'];
                        echo ($eval > 0 ? '+' : '') . number_format($eval, 1);
                        ?>
                    </div>
                    <div class="comments">
                        <strong>Bobby's Comments:</strong>
                        <ul>
                            <?php foreach ($_SESSION['analysis']['comments'] as $comment): ?>
                                <li><?php echo htmlspecialchars($comment); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p>Click "Analyze Position" to get Bobby's insights.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <script>
        var board = null;
        var game = new Chess('<?php echo $_SESSION['game_state']; ?>');
        var $status = $('#status');
        var $pgn = $('#pgn');

        function onDragStart(source, piece, position, orientation) {
            if (game.game_over()) return false;
            if ((game.turn() === 'w' && piece.search(/^b/) !== -1) ||
                (game.turn() === 'b' && piece.search(/^w/) !== -1)) {
                return false;
            }
        }

        function onDrop(source, target) {
            var move = game.move({
                from: source,
                to: target,
                promotion: 'q'
            });

            if (move === null) return 'snapback';

            updateStatus();
            
            $.post('index.php', {
                update_position: true,
                fen: game.fen(),
                pgn: game.history().join(',')
            });
        }

        function updateStatus() {
            var status = '';
            var moveColor = game.turn() === 'b' ? 'Black' : 'White';

            if (game.in_checkmate()) {
                status = 'Game over, ' + moveColor + ' is in checkmate.';
            } else if (game.in_draw()) {
                status = 'Game over, drawn position';
            } else {
                status = moveColor + ' to move';
                if (game.in_check()) {
                    status += ', ' + moveColor + ' is in check';
                }
            }

            $status.html(status);
            $pgn.html(game.pgn({ max_width: 5, newline_char: '<br />' }));
        }

        var config = {
            draggable: true,
            position: '<?php echo $_SESSION['game_state']; ?>',
            onDragStart: onDragStart,
            onDrop: onDrop,
            onSnapEnd: function() {
                board.position(game.fen());
            },
            pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png'
        };
        board = Chessboard('board', config);

        $('#flipBtn').on('click', board.flip);
        $('#undoBtn').on('click', function() {
            game.undo();
            board.position(game.fen());
            $.post('index.php', {
                update_position: true,
                fen: game.fen(),
                pgn: game.history().join(',')
            });
            updateStatus();
        });

        updateStatus();

        $('button[name="analyze"]').on('click', function(e) {
            e.preventDefault();
            $.post('index.php', {
                analyze: true,
                position: game.fen()
            }, function() {
                location.reload();
            });
        });

        $('button[name="save_position"]').on('click', function(e) {
            e.preventDefault();
            $.post('index.php', {
                save_position: true,
                position: game.fen()
            }, function() {
                alert('Position saved!');
            });
        });
    </script>
</body>
</html>