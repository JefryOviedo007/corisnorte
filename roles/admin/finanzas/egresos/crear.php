<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../../../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$concepto      = trim($_POST['concepto'] ?? '');
$beneficiario  = trim($_POST['proveedor_nombre'] ?? '');
$metodo        = $_POST['metodo_pago'] ?? 'Efectivo';
$referencia    = trim($_POST['referencia'] ?? '');
$persona_id    = !empty($_POST['persona_id']) ? (int)$_POST['persona_id'] : null;
$usuario_id    = $_SESSION['id'] ?? null;

// Montos
$monto_total = $efectivo = $transferencia = 0;

if ($metodo === 'Dividido') {
    $efectivo      = (float)($_POST['monto_efectivo'] ?? 0);
    $transferencia = (float)($_POST['monto_transferencia'] ?? 0);
    $monto_total   = $efectivo + $transferencia;
} else {
    $monto_total   = (float)($_POST['monto_total'] ?? 0);
    $efectivo      = ($metodo === 'Efectivo')      ? $monto_total : 0;
    $transferencia = ($metodo === 'Transferencia') ? $monto_total : 0;
}

// Validaciones
if (empty($concepto)) {
    echo json_encode(['status' => 'error', 'message' => 'El concepto es obligatorio.']);
    exit;
}
if ($monto_total <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'El monto debe ser mayor a cero.']);
    exit;
}
if (!$usuario_id) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida. Por favor inicia sesión nuevamente.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO egresos (
                persona_id, concepto, monto, monto_efectivo,
                monto_transferencia, metodo_pago, referencia,
                observaciones, usuario_id, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $persona_id,    // null si no se vinculó persona
        $concepto,
        $monto_total,
        $efectivo,
        $transferencia,
        $metodo,
        $referencia,
        $beneficiario,  // nombre libre si no se vinculó persona
        $usuario_id     // ← quien registró el movimiento
    ]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Egreso registrado con éxito.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}