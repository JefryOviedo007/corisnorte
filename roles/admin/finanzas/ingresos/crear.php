<?php
session_start();
header('Content-Type: application/json');

// Ajusta la ruta a tu archivo de conexión
require_once __DIR__ . "/../../../../config.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Recoger y sanitizar datos básicos
    $concepto = trim($_POST['concepto'] ?? '');
    $observaciones = trim($_POST['cliente_nombre'] ?? ''); // Guardamos el nombre del tercero aquí
    $metodo = $_POST['metodo_pago'] ?? 'Efectivo';
    $referencia = trim($_POST['referencia'] ?? '');
    $usuario_id = $_SESSION['usuario_id'] ?? null; // ID del usuario logueado
    
    // 2. Lógica de Montos
    $monto_total = 0;
    $efectivo = 0;
    $transferencia = 0;

    if ($metodo === 'Dividido') {
        $efectivo = (float)($_POST['monto_efectivo'] ?? 0);
        $transferencia = (float)($_POST['monto_transferencia'] ?? 0);
        $monto_total = $efectivo + $transferencia;
    } elseif ($metodo === 'Efectivo') {
        $monto_total = (float)($_POST['monto_total'] ?? 0);
        $efectivo = $monto_total;
        $transferencia = 0;
    } elseif ($metodo === 'Transferencia') {
        $monto_total = (float)($_POST['monto_total'] ?? 0);
        $transferencia = $monto_total;
        $efectivo = 0;
    }

    // 3. Validaciones de seguridad
    if (empty($concepto)) {
        echo json_encode(['status' => 'error', 'message' => 'El concepto es obligatorio.']);
        exit;
    }

    if ($monto_total <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'El monto total debe ser mayor a cero.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 4. Preparar la consulta SQL según tu estructura
        $sql = "INSERT INTO ingresos (
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
        
        // Ejecutar con los valores recogidos
        $stmt->execute([
            null,           // persona_id: por ahora null ya que es un ingreso general de texto
            $concepto,      // concepto
            $monto_total,   // monto
            $efectivo,      // monto_efectivo
            $transferencia, // monto_transferencia
            $metodo,        // metodo_pago
            $referencia,    // referencia
            $observaciones, // observaciones (Nombre del cliente/tercero)
            $usuario_id     // usuario_id (quien registra)
        ]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Ingreso registrado correctamente.'
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al guardar: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}