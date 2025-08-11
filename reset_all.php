<?php
session_start();
require_once 'db.php';
$_SESSION['scores'] = ['X' => 0, 'O' => 0, 'Draw' => 0];
$stmt = $mysqli->prepare("SELECT id FROM scores LIMIT 1");
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    $ins = $mysqli->prepare("INSERT INTO scores (player_x, player_o, score_x, score_o, draws) VALUES (?, ?, 0, 0, 0)");
    $pX = $_SESSION['players']['X'] ?? 'Player X';
    $pO = $_SESSION['players']['O'] ?? 'Player O';
    $ins->bind_param("ss", $pX, $pO);
    $ins->execute();
    $ins->close();
} else {
    $row = $res->fetch_assoc();
    $id = (int)$row['id'];
    $stmt->close();
    $up = $mysqli->prepare("UPDATE scores SET score_x = 0, score_o = 0, draws = 0 WHERE id = ?");
    $up->bind_param("i", $id);
    $up->execute();
    $up->close();
}
header('Location: index.php');
exit;
