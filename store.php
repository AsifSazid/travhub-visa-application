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
        INSERT INTO applications (`uuid`, `application_data`, `timestamp`)
        VALUES (:uuid, :data, :ts)
        ON DUPLICATE KEY UPDATE
            application_data = :data,
            timestamp = :ts
    ");

    $stmt->execute([
        ':uuid' => $data['uuid'],
        ':data' => json_encode($data),
        ':ts'   => date('Y-m-d H:i:s')
    ]);

    echo json_encode(["status" => "success", "message" => "Data saved/updated successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$pdo = null;
