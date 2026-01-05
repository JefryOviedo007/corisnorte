<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . "/../../../../config.php";

header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Buscamos directamente en la tabla personas por su ID
        $stmt = $pdo->prepare("SELECT * FROM personas WHERE id = ?");
        $stmt->execute([$id]);
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($persona) {
            echo json_encode(['status' => 'success', 'data' => $persona]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Estudiante no encontrado en la base de datos']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
}
exit;