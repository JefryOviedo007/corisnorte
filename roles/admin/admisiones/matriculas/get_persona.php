<?php
// Evitar que errores o warnings rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . "/../../../../config.php";

if (ob_get_length()) ob_clean();
header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // La consulta con p.* ya obtiene: foto, direccion, sisben, eps, etc.
        $stmt = $pdo->prepare("
            SELECT p.* FROM personas p 
            INNER JOIN inscripciones i ON i.persona_id = p.id 
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($persona) {
            echo json_encode(['status' => 'success', 'data' => $persona]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Persona no encontrada']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
}
exit;