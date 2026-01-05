<?php
session_start();
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $persona_id = $_POST['persona_id'] ?? null;
    $concepto_recibido = $_POST['concepto'] ?? 'Pensión'; 
    $monto_recibido = (float)($_POST['monto'] ?? 0); // Valor digitado en el modal (puede ser abono)
    $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';
    
    // Estos vienen del modal solo si se usó "Dividido"
    $m_efectivo_post = (float)($_POST['monto_efectivo'] ?? 0);
    $m_transferencia_post = (float)($_POST['monto_transferencia'] ?? 0);
    
    $referencia_manual = $_POST['referencia'] ?? '';

    if (!$persona_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID de persona no recibido']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Obtener datos de la Institución
        $stmtInst = $pdo->query("SELECT * FROM institucion LIMIT 1");
        $inst = $stmtInst->fetch(PDO::FETCH_ASSOC);

        // 2. Obtener datos detallados del estudiante e inscripción
        $sqlInfo = "SELECT 
                        p.id AS persona_id, p.nombres_completos, p.correo, p.numero_documento,
                        i.id AS insc_id, i.codigo, 
                        prog.nombre AS programa,
                        s.nombre AS sede, s.direccion AS sede_dir
                    FROM personas p 
                    INNER JOIN inscripciones i ON i.persona_id = p.id 
                    INNER JOIN grupos g ON i.grupo_id = g.id
                    INNER JOIN programas prog ON g.programa_id = prog.id
                    INNER JOIN sedes s ON g.sede_id = s.id
                    WHERE p.id = ? AND i.estado = 'En formación'
                    LIMIT 1";
        
        $stmt = $pdo->prepare($sqlInfo);
        $stmt->execute([$persona_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) throw new Exception("No se encontró inscripción activa para este estudiante.");

        // --- LÓGICA DE MAPEO PARA EL ENUM ---
        $concepto_enum = 'Otros';
        if (stripos($concepto_recibido, 'Pensi') !== false || stripos($concepto_recibido, 'Mes') !== false) {
            $concepto_enum = 'Pensión';
        } elseif (stripos($concepto_recibido, 'Matrícula') !== false || stripos($concepto_recibido, 'Matricula') !== false) {
            $concepto_enum = 'Matrícula';
        } elseif (stripos($concepto_recibido, 'Grado') !== false) {
            $concepto_enum = 'Derechos de Grado';
        } elseif (stripos($concepto_recibido, 'Seminario') !== false) {
            $concepto_enum = 'Seminario';
        }

        // --- DISTRIBUCIÓN DE MONTOS FLEXIBLE (PERMITE ABONOS) ---
        $efectivo = 0;
        $transferencia = 0;
        $monto_final = 0;

        if ($metodo_pago === 'Dividido') {
            // En pago dividido, el total es la suma de los dos campos ingresados
            $efectivo = $m_efectivo_post;
            $transferencia = $m_transferencia_post;
            $monto_final = $efectivo + $transferencia;
        } elseif ($metodo_pago === 'Efectivo') {
            $efectivo = $monto_recibido;
            $transferencia = 0;
            $monto_final = $monto_recibido;
        } elseif ($metodo_pago === 'Transferencia') {
            $efectivo = 0;
            $transferencia = $monto_recibido;
            $monto_final = $monto_recibido;
        }

        if ($monto_final <= 0) {
            throw new Exception("El monto a pagar debe ser mayor a cero.");
        }

        // 3. Preparar el insert de Pago
        $insPago = $pdo->prepare("INSERT INTO pagos (persona_id, inscripcion_id, concepto, monto, monto_efectivo, monto_transferencia, metodo_pago, referencia, observaciones, estado) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completado')");

        $referencia = !empty($referencia_manual) ? $referencia_manual : "REC-" . $data['codigo'] . "-" . date('His');

        // Ejecutar Insert
        $insPago->execute([
            $data['persona_id'], 
            $data['insc_id'], 
            $concepto_enum, 
            $monto_final,   // El valor total del abono o pago total
            $efectivo,      
            $transferencia, 
            $metodo_pago, 
            $referencia,
            $concepto_recibido // Detalle: "Pensión - Mes 1"
        ]);

        $pago_id = $pdo->lastInsertId();

        // 4. Enviar correo informativo con el monto real pagado
        enviarCorreoMensualidad($data, $inst, $referencia, $metodo_pago, $efectivo, $transferencia, $concepto_recibido, $monto_final);

        $pdo->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => 'Pago de ' . $concepto_recibido . ' por $' . number_format($monto_final, 0) . ' procesado correctamente.', 
            'correo_envio' => $data['correo']
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function enviarCorreoMensualidad($data, $inst, $referencia, $metodo, $efectivo, $transf, $concepto, $total) {
    $costoFmt = '$ ' . number_format($total, 0, ',', '.');
    $fecha = date('d/m/Y h:i A');
    
    $subject = "Comprobante de Pago - " . $concepto . " - " . $data['codigo'];
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Tesorería Corisnorte <admisiones@corisnorte.com>\r\n";

    $pagoDetalle = ($metodo === 'Dividido') 
        ? "Efectivo: $".number_format($efectivo,0)." / Transf: $".number_format($transf,0) 
        : $metodo;

    $message = '
    <html>
    <body style="margin:0;padding:0;background-color:#f4f7f9;font-family:Arial,sans-serif;">
    <table width="100%" bgcolor="#f4f7f9" style="padding:20px;">
        <tr><td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                <tr>
                    <td style="background:#8FBCE6;padding:25px 20px;text-align:center;">
                        <img src="https://dev.corisnorte.com/assets/img/logo-corisnorte-text.png" alt="CORISNORTE" style="max-height:65px;display:block;margin:0 auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:35px;color:#333;">
                        <h2 style="color:#0D09A4; text-align:center; margin-bottom:10px;">Comprobante de Pago Recibido</h2>
                        <p style="text-align:center; color:#666; margin-bottom:25px;">Hemos registrado exitosamente tu pago por concepto de '.$concepto.'.</p>
                        
                        <p>Estimado(a) <strong>'.htmlspecialchars($data['nombres_completos']).'</strong>,</p>
                        <p>A continuación, adjuntamos los detalles de tu transacción:</p>

                        <div style="background:#fff; border:1px solid #ddd; border-radius:10px; margin:30px 0; overflow:hidden; font-family:Courier New, Courier, monospace;">
                            <div style="background:#f8f9fa; border-bottom:1px dashed #ccc; padding:15px; text-align:center;">
                                <strong style="font-size:18px;">RECIBO DE CAJA</strong><br>
                                <span style="font-size:12px; color:#666;">Ref: '.$referencia.'</span>
                            </div>
                            <div style="padding:20px; font-size:14px; line-height:1.6;">
                                <div style="text-align:center; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
                                    <strong>'.htmlspecialchars($inst['nombre']).'</strong><br>
                                    Tel: '.htmlspecialchars($inst['telefono']).'
                                </div>
                                
                                <table width="100%">
                                    <tr><td><strong>Fecha:</strong></td> <td align="right">'.$fecha.'</td></tr>
                                    <tr><td><strong>Estudiante:</strong></td> <td align="right">'.htmlspecialchars($data['nombres_completos']).'</td></tr>
                                    <tr><td><strong>Programa:</strong></td> <td align="right">'.htmlspecialchars($data['programa']).'</td></tr>
                                    <tr><td colspan="2" style="border-bottom:1px dashed #ccc; padding:5px 0;"></td></tr>
                                    <tr><td style="padding-top:10px;"><strong>Concepto:</strong></td> <td align="right" style="padding-top:10px;">'.$concepto.'</td></tr>
                                    <tr><td><strong>Método:</strong></td> <td align="right">'.$pagoDetalle.'</td></tr>
                                    <tr><td style="font-size:20px; padding-top:15px;"><strong>TOTAL:</strong></td> <td align="right" style="font-size:20px; padding-top:15px;"><strong>'.$costoFmt.'</strong></td></tr>
                                </table>
                            </div>
                            <div style="background:#0D09A4; color:#fff; padding:10px; text-align:center; font-size:12px;">
                                SOPORTE DE PAGO ELECTRÓNICO
                            </div>
                        </div>

                        <p style="font-size:13px; color:#718096; margin-top:30px; border-top:1px solid #edf2f7; padding-top:20px;">
                            Atentamente,<br>
                            <strong>Tesorería y Registro - Corisnorte</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#8FBCE6;padding:25px 15px;text-align:center;font-size:12px;color:#0D09A4;">
                         © '.date('Y').' '.htmlspecialchars($inst['nombre']).'
                    </td>
                </tr>
            </table>
        </td></tr>
    </table>
    </body>
    </html>';

    mail($data['correo'], $subject, $message, $headers);
}