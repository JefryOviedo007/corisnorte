<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../../../config.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $concepto = trim($_POST['concepto'] ?? '');
    $beneficiario = trim($_POST['proveedor_nombre'] ?? ''); 
    $metodo = $_POST['metodo_pago'] ?? 'Efectivo';
    $referencia = trim($_POST['referencia'] ?? '');
    $usuario_id = $_SESSION['usuario_id'] ?? null; 
    
    $monto_total = 0;
    $efectivo = 0;
    $transferencia = 0;

    if ($metodo === 'Dividido') {
        $efectivo = (float)($_POST['monto_efectivo'] ?? 0);
        $transferencia = (float)($_POST['monto_transferencia'] ?? 0);
        $monto_total = $efectivo + $transferencia;
    } else {
        $monto_total = (float)($_POST['monto_total'] ?? 0);
        $efectivo = ($metodo === 'Efectivo') ? $monto_total : 0;
        $transferencia = ($metodo === 'Transferencia') ? $monto_total : 0;
    }

    if (empty($concepto) || $monto_total <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos o monto inválido.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Nota: Asegúrate que los nombres de las columnas coincidan con tu tabla 'egresos'
        $sql = "INSERT INTO egresos (
                    persona_id, 
                    concepto, 
                    monto, 
                    monto_efectivo, 
                    monto_transferencia, 
                    metodo_pago, 
                    referencia, 
                    observaciones, 
                    usuario_id, 
                    estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            null, 
            $concepto, 
            $monto_total, 
            $efectivo, 
            $transferencia, 
            $metodo, 
            $referencia, 
            $beneficiario, // Se guarda en observaciones quien recibió el dinero
            $usuario_id
        ]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Egreso registrado con éxito.']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}