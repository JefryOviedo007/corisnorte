<?php
// obtener_programas.php
// Este archivo maneja la petición AJAX para obtener programas activos basados en el nivel_id seleccionado.

require_once __DIR__ . "/../../../../config.php";

$nivel_id = $_GET['nivel_id'] ?? null;

// Validar que nivel_id sea un entero válido
if (!$nivel_id || !is_numeric($nivel_id)) {
    echo json_encode([]);
    exit;
}

$nivel_id = (int) $nivel_id;

// Consulta para obtener programas activos del nivel seleccionado
$sql = "SELECT id, nombre FROM programas WHERE nivel_id = ? AND estado = 'Activo' ORDER BY nombre";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nivel_id]);
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($programas);
?>
