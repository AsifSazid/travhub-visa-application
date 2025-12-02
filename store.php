<?php
header("Content-Type: application/json");
require 'server/db_connection.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (`uuid`, `applicantion_data`, `timestamp`)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $data['uuid'],
        json_encode($data),
        date('Y-m-d H:i:s') // use current timestamp
    ]);

    echo json_encode(["status" => "success", "message" => "Data saved successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$pdo = null;
