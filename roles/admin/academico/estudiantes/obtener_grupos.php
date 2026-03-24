<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, nombre, jornada, cupos_disponibles FROM grupos WHERE estado != 'Cerrado' ORDER BY nombre ASC");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}