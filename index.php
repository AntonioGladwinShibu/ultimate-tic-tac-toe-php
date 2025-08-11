<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['board']) || !is_array($_SESSION['board'])) $_SESSION['board'] = array_fill(0,9,'');
if (!isset($_SESSION['turn'])) $_SESSION['turn'] = 'X';
if (!isset($_SESSION['winner'])) $_SESSION['winner'] = null;
if (!isset($_SESSION['win_line']) || !is_array($_SESSION['win_line'])) $_SESSION['win_line'] = [];
if (!isset($_SESSION['players']) || !is_array($_SESSION['players'])) $_SESSION['players'] = ['X' => 'Player X', 'O' => 'Player O'];
if (!isset($_SESSION['scores']) || !is_array($_SESSION['scores'])) $_SESSION['scores'] = ['X' => 0, 'O' => 0, 'Draw' => 0];
if (!isset($_SESSION['last_winner'])) $_SESSION['last_winner'] = null;
if (!isset($_SESSION['round_reset'])) $_SESSION['round_reset'] = false;
function safe($v){return htmlspecialchars($v ?? '',ENT_QUOTES,'UTF-8');}
function check_winner($b){
    $wins = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
    foreach($wins as $c){
        [$a,$d,$e] = $c;
        if ($b[$a] !== '' && $b[$a] === $b[$d] && $b[$d] === $b[$e]) return ['winner'=>$b[$a],'line'=>$c];
    }
    if (!in_array('', $b, true)) return ['winner'=>'Draw','line'=>[]];
    return ['winner'=>null,'line'=>[]];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'names') {
    $x = trim($_POST['playerX'] ?? '');
    $o = trim($_POST['playerO'] ?? '');
    if ($x !== '') $_SESSION['players']['X'] = $x;
    if ($o !== '') $_SESSION['players']['O'] = $o;
    $stmt = $mysqli->prepare("SELECT id FROM scores LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        $ins = $mysqli->prepare("INSERT INTO scores (player_x, player_o, score_x, score_o, draws) VALUES (?, ?, 0, 0, 0)");
        $ins->bind_param("ss", $_SESSION['players']['X'], $_SESSION['players']['O']);
        $ins->execute();
        $ins->close();
    } else {
        $row = $res->fetch_assoc();
        $id = (int)$row['id'];
        $stmt->close();
        $up = $mysqli->prepare("UPDATE scores SET player_x = ?, player_o = ? WHERE id = ?");
        $up->bind_param("ssi", $_SESSION['players']['X'], $_SESSION['players']['O'], $id);
        $up->execute();
        $up->close();
    }
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['result'])) {
    $res = $_POST['result'];
    if ($res === 'X') $_SESSION['scores']['X']++;
    elseif ($res === 'O') $_SESSION['scores']['O']++;
    elseif ($res === 'Draw') $_SESSION['scores']['Draw']++;
    $winnerName = ($res === 'Draw') ? 'No one' : ($_SESSION['players'][$res] ?? $res);
    $ins = $mysqli->prepare("INSERT INTO matches (winner) VALUES (?)");
    $ins->bind_param("s", $winnerName);
    $ins->execute();
    $ins->close();
    $stmt = $mysqli->prepare("SELECT id FROM scores LIMIT 1");
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r->num_rows === 0) {
        $stmt->close();
        $ins2 = $mysqli->prepare("INSERT INTO scores (player_x, player_o, score_x, score_o, draws) VALUES (?, ?, ?, ?, ?)");
        $sx = (int)$_SESSION['scores']['X'];
        $so = (int)$_SESSION['scores']['O'];
        $sd = (int)$_SESSION['scores']['Draw'];
        $ins2->bind_param("ssiii", $_SESSION['players']['X'], $_SESSION['players']['O'], $sx, $so, $sd);
        $ins2->execute();
        $ins2->close();
    } else {
        $row = $r->fetch_assoc();
        $id = (int)$row['id'];
        $stmt->close();
        $up2 = $mysqli->prepare("UPDATE scores SET player_x = ?, player_o = ?, score_x = ?, score_o = ?, draws = ? WHERE id = ?");
        $sx = (int)$_SESSION['scores']['X'];
        $so = (int)$_SESSION['scores']['O'];
        $sd = (int)$_SESSION['scores']['Draw'];
        $up2->bind_param("ssiiii", $_SESSION['players']['X'], $_SESSION['players']['O'], $sx, $so, $sd, $id);
        $up2->execute();
        $up2->close();
    }
    $_SESSION['last_winner'] = $winnerName;
    echo 'OK';
    exit;
}
$row = $mysqli->query("SELECT * FROM scores LIMIT 1");
$scoresRow = $row ? $row->fetch_assoc() : null;
$players = $_SESSION['players'];
$scores = $_SESSION['scores'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Tic Tac Toe</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header"><h1>Tic Tac Toe</h1></header>
<main class="main">
<section class="setup">
<form method="post">
<input type="hidden" name="action" value="names">
<label>Player X: <input name="playerX" value="<?= safe($players['X']) ?>"></label>
<label>Player O: <input name="playerO" value="<?= safe($players['O']) ?>"></label>
<button class="btn primary" type="submit">Start / Apply Names</button>
</form>
<div class="scoreboard">
<div><strong id="labelX"><?= safe($players['X']) ?></strong> <span id="scoreX"><?= (int)$scores['X'] ?></span></div>
<div><strong id="labelO"><?= safe($players['O']) ?></strong> <span id="scoreO"><?= (int)$scores['O'] ?></span></div>
<div><strong>Draws</strong> <span id="scoreD"><?= (int)$scores['Draw'] ?></span></div>
</div>
<div id="board" class="board" role="grid"></div>
<div id="status" class="status">Press a cell to start</div>
<div class="controls-bottom">
<a href="reset_round.php" class="btn">Reset Round</a>
<a href="reset_all.php" class="btn">Reset Score</a>
<a href="exit.php" class="btn danger">Exit</a>
</div>
</section>
<section class="extras">
<div class="history">
<h3>Recent Matches</h3>
<ol>
<?php
$res = $mysqli->query("SELECT * FROM matches ORDER BY match_date DESC LIMIT 10");
while ($r = $res->fetch_assoc()) {
    echo '<li>' . safe($r['match_date']) . ' — Winner: ' . safe($r['winner']) . '</li>';
}
$res->close();
?>
</ol>
</div>
</section>
</main>
<?php include 'footer.php'; ?>
<script src="app.js"></script>
<script>
window.TTT = { players: { X: <?= json_encode($players['X']) ?>, O: <?= json_encode($players['O']) ?> }, scores: { X: <?= (int)$scores['X'] ?>, O: <?= (int)$scores['O'] ?>, D: <?= (int)$scores['Draw'] ?> }, lastWinner: <?= json_encode($_SESSION['last_winner'] ?? '') ?> };
</script>
</body>
</html>
