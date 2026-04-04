<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../../../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$id        = (int)($_POST['id']        ?? 0);
$descuento = (int)($_POST['descuento'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Estudiante no válido.']);
    exit;
}

if ($descuento < 0 || $descuento > 100) {
    echo json_encode(['status' => 'error', 'message' => 'El descuento debe estar entre 0 y 100.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE personas SET descuento = ? WHERE id = ?");
    $stmt->execute([$descuento, $id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Estudiante no encontrado.']);
        exit;
    }

    echo json_encode([
        'status'  => 'success',
        'message' => "Descuento del {$descuento}% aplicado correctamente."
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}