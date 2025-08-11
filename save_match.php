<?php
require_once 'db.php';
session_start();
$payload = json_decode(file_get_contents('php://input'), true);
$playerX = $payload['playerX'] ?? ($_SESSION['players']['X'] ?? 'Player X');
$playerO = $payload['playerO'] ?? ($_SESSION['players']['O'] ?? 'Player O');
$winner = $payload['winner'] ?? 'No one';
$stmt = $mysqli->prepare("INSERT INTO matches (winner) VALUES (?)");
$stmt->bind_param("s", $winner);
$stmt->execute();
$stmt->close();
echo json_encode(['ok'=>true]);
