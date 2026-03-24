<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

$rol = $_SESSION['rol'] ?? '';
$sede_id_session = $_SESSION['sede_id'] ?? null;
// Capturamos la sede que viene por el filtro del modal (si existe)
$sede_filtro = $_GET['sede_id'] ?? null;

try {
    $sql = "SELECT id, nombre, jornada, cupos_disponibles 
            FROM grupos 
            WHERE estado != 'Finalizado'";
    
    $params = [];

    if ($rol === 'Admin') {
        // Si el admin eligió una sede específica en el select del modal
        if (!empty($sede_filtro)) {
            $sql .= " AND sede_id = ?";
            $params[] = $sede_filtro;
        }
    } else {
        // Si es coordinador, forzamos siempre su sede de sesión
        $sql .= " AND sede_id = ?";
        $params[] = $sede_id_session;
    }

    $sql .= " ORDER BY nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode([
        'status' => 'success', 
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}