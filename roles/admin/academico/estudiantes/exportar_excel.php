<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

try {
    // Seleccionamos todas las columnas de la tabla personas
    $stmt = $pdo->query("SELECT * FROM personas ORDER BY nombres_completos ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
exit;