<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . "/../../../../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

// 1. Datos básicos
$concepto      = trim($_POST['concepto'] ?? '');
$observaciones = trim($_POST['cliente_nombre'] ?? '');
$metodo        = $_POST['metodo_pago'] ?? 'Efectivo';
$referencia    = trim($_POST['referencia'] ?? '');
$persona_id    = !empty($_POST['persona_id']) ? (int)$_POST['persona_id'] : null;
$usuario_id    = $_SESSION['id'] ?? null; // Asegúrate que en tu login guardas $_SESSION['usuario_id']

// 2. Montos
$monto_total = $efectivo = $transferencia = 0;

if ($metodo === 'Dividido') {
    $efectivo      = (float)($_POST['monto_efectivo'] ?? 0);
    $transferencia = (float)($_POST['monto_transferencia'] ?? 0);
    $monto_total   = $efectivo + $transferencia;
} elseif ($metodo === 'Efectivo') {
    $monto_total = (float)($_POST['monto_total'] ?? 0);
    $efectivo    = $monto_total;
} elseif ($metodo === 'Transferencia') {
    $monto_total   = (float)($_POST['monto_total'] ?? 0);
    $transferencia = $monto_total;
}

// 3. Validaciones
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

// 4. Insertar
try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO ingresos (
                persona_id, concepto, monto, monto_efectivo,
                monto_transferencia, metodo_pago, referencia,
                observaciones, usuario_id, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $persona_id,    // null si no se seleccionó nadie
        $concepto,
        $monto_total,
        $efectivo,
        $transferencia,
        $metodo,
        $referencia,
        $observaciones, // nombre libre si no se vinculó persona
        $usuario_id     // ← ahora sí se guarda correctamente
    ]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ingreso registrado correctamente.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
}