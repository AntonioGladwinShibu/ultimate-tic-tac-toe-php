<?php
session_start();

$_SESSION['board'] = array_fill(0, 9, '');
$_SESSION['turn'] = 'X';
$_SESSION['winner'] = null;
$_SESSION['win_line'] = [];
$_SESSION['round_reset'] = false;

header('Location: index.php');
exit;
?>
