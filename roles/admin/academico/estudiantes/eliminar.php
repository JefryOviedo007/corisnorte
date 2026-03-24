<?php
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

// Ahora leemos desde $_POST porque usamos FormData en el JS
$ids_json = $_POST['ids'] ?? null;
$ids = json_decode($ids_json, true);

if (!$ids || !is_array($ids)) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron IDs válidos']);
    exit;
}

try {
    $pdo->beginTransaction();
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    // Borrar fotos físicas
    $stmtFotos = $pdo->prepare("SELECT foto FROM personas WHERE id IN ($placeholders)");
    $stmtFotos->execute($ids);
    $fotos = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);
    foreach ($fotos as $foto) {
        if (!empty($foto)) {
            $ruta = __DIR__ . "/uploads/" . $foto;
            if (file_exists($ruta)) @unlink($ruta);
        }
    }

    // Aquí borras tablas relacionadas (Cascada manual)
    $pdo->prepare("DELETE FROM pagos WHERE persona_id IN ($placeholders)")->execute($ids);

    // Borrar de la tabla principal
    $stmt = $pdo->prepare("DELETE FROM personas WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => count($ids) . ' registro(s) eliminado(s)']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}