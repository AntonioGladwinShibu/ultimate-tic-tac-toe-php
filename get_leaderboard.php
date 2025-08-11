<?php
require_once 'db.php';
$stmt = $mysqli->query("SELECT winner, COUNT(*) AS wins FROM matches WHERE winner != 'No one' GROUP BY winner ORDER BY wins DESC LIMIT 10");
$rows = [];
while ($r = $stmt->fetch_assoc()) $rows[] = $r;
header('Content-Type: application/json');
echo json_encode($rows);
