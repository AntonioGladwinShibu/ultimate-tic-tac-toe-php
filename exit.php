<?php
session_start();
$winnerName = $_SESSION['last_winner'] ?? 'No one';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Tic Tac Toe - Exit</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="page-exit">
<header class="site-header"><h1>Tic Tac Toe</h1></header>
<main class="main">
<section class="exit-card">
<?php if ($winnerName === 'Draw' || $winnerName === 'No one'): ?>
<h2>It's a draw! 🎮</h2>
<?php else: ?>
<h2>🎉 Congratulations, <?= htmlspecialchars($winnerName, ENT_QUOTES, 'UTF-8') ?>! 🎉</h2>
<p>You are the final winner.</p>
<?php endif; ?>
<p class="thank-you">Thank you for playing Tic Tac Toe!</p>
<div class="exit-actions">
<a href="index.php" class="btn primary">Play Again</a>
<a href="reset_all.php" class="btn">Reset Score</a>
</div>
</section>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
