<?php
session_start();
require_once __DIR__ . "/../../../../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = json_decode($_POST['ids']);
    $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';
    $m_efectivo_post = (float)($_POST['monto_efectivo'] ?? 0);
    $m_transferencia_post = (float)($_POST['monto_transferencia'] ?? 0);
    
    try {
        $pdo->beginTransaction();

        // 1. Obtener datos de la Institución para el recibo
        $stmtInst = $pdo->query("SELECT * FROM institucion LIMIT 1");
        $inst = $stmtInst->fetch(PDO::FETCH_ASSOC);

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 2. Obtener datos detallados de estudiantes, sedes y programas
        $sqlInfo = "SELECT 
                        p.id AS persona_id, p.nombres_completos, p.correo, p.numero_documento,
                        i.id AS insc_id, i.codigo, 
                        prog.nombre AS programa, prog.costo_matricula,
                        s.nombre AS sede, s.direccion AS sede_dir
                    FROM inscripciones i 
                    INNER JOIN personas p ON i.persona_id = p.id 
                    INNER JOIN grupos g ON i.grupo_id = g.id
                    INNER JOIN programas prog ON g.programa_id = prog.id
                    INNER JOIN sedes s ON g.sede_id = s.id
                    WHERE i.id IN ($placeholders)";
        
        $stmt = $pdo->prepare($sqlInfo);
        $stmt->execute($ids);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updInsc = $pdo->prepare("UPDATE inscripciones SET estado = 'En formación' WHERE id = ?");
        $updPers = $pdo->prepare("UPDATE personas SET estado = 'En formación' WHERE id = ?");
        $insPago = $pdo->prepare("INSERT INTO pagos (persona_id, inscripcion_id, concepto, monto, monto_efectivo, monto_transferencia, metodo_pago, referencia, estado) 
                                 VALUES (?, ?, 'Matrícula', ?, ?, ?, ?, ?, 'Completado')");

        foreach ($estudiantes as $data) {
            $costo = (float)$data['costo_matricula'];
            $efectivo = ($metodo_pago === 'Dividido') ? $m_efectivo_post : ($metodo_pago === 'Efectivo' ? $costo : 0);
            $transferencia = ($metodo_pago === 'Dividido') ? $m_transferencia_post : ($metodo_pago === 'Transferencia' ? $costo : 0);
            
            $referencia = "MAT-" . $data['codigo'];

            // Ejecutar Updates e Insert de Pago
            $updInsc->execute([$data['insc_id']]);
            $updPers->execute([$data['persona_id']]);
            $insPago->execute([$data['persona_id'], $data['insc_id'], $costo, $efectivo, $transferencia, $metodo_pago, $referencia]);

            // Enviar correo con el nuevo diseño y datos de la institución
            enviarCorreoMatricula($data, $inst, $referencia, $metodo_pago, $efectivo, $transferencia);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Matrícula y pago legalizados correctamente.']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function enviarCorreoMatricula($data, $inst, $referencia, $metodo, $efectivo, $transf) {
    $costoFmt = '$ ' . number_format($data['costo_matricula'], 0, ',', '.');
    $fecha = date('d/m/Y h:i A');
    
    $subject = "¡Matrícula Exitosa! - Comprobante de Pago " . $data['codigo'];
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Tesorería Corisnorte <admisiones@corisnorte.com>\r\n";

    // Lógica para detalle del método de pago en el recibo
    $pagoDetalle = "";
    if($metodo === 'Dividido'){
        $pagoDetalle = "Efectivo: $".number_format($efectivo,0)." / Transf: $".number_format($transf,0);
    } else {
        $pagoDetalle = $metodo;
    }

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
                        <h2 style="color:#0D09A4; text-align:center; margin-bottom:10px;">¡Bienvenido(a) a la Institución!</h2>
                        <p style="text-align:center; color:#666; margin-bottom:25px;">Tu proceso de matrícula académica ha sido completado con éxito.</p>
                        
                        <p>Estimado(a) <strong>'.htmlspecialchars($data['nombres_completos']).'</strong>,</p>
                        <p>Es un gusto confirmar que ya eres parte de <strong>'.htmlspecialchars($inst['nombre']).'</strong>. A continuación, adjuntamos tu comprobante digital de pago:</p>

                        <div style="background:#fff; border:1px solid #ddd; border-radius:10px; margin:30px 0; overflow:hidden; font-family:Courier New, Courier, monospace;">
                            <div style="background:#f8f9fa; border-bottom:1px dashed #ccc; padding:15px; text-align:center;">
                                <strong style="font-size:18px;">COMPROBANTE DE PAGO</strong><br>
                                <span style="font-size:12px; color:#666;">Ref: '.$referencia.'</span>
                            </div>
                            <div style="padding:20px; font-size:14px; line-height:1.6;">
                                <div style="text-align:center; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
                                    <strong>'.htmlspecialchars($inst['nombre']).'</strong><br>
                                    '.htmlspecialchars($inst['direccion']).' - '.htmlspecialchars($inst['ciudad']).'<br>
                                    Tel: '.htmlspecialchars($inst['telefono']).'
                                </div>
                                
                                <table width="100%">
                                    <tr><td><strong>Fecha:</strong></td> <td align="right">'.$fecha.'</td></tr>
                                    <tr><td><strong>Estudiante:</strong></td> <td align="right">'.htmlspecialchars($data['nombres_completos']).'</td></tr>
                                    <tr><td><strong>Documento:</strong></td> <td align="right">'.$data['numero_documento'].'</td></tr>
                                    <tr><td><strong>Programa:</strong></td> <td align="right">'.htmlspecialchars($data['programa']).'</td></tr>
                                    <tr><td><strong>Sede:</strong></td> <td align="right">'.htmlspecialchars($data['sede']).'</td></tr>
                                    <tr><td colspan="2" style="border-bottom:1px dashed #ccc; padding:5px 0;"></td></tr>
                                    <tr><td style="padding-top:10px;"><strong>Concepto:</strong></td> <td align="right" style="padding-top:10px;">Matrícula Académica</td></tr>
                                    <tr><td><strong>Método:</strong></td> <td align="right">'.$pagoDetalle.'</td></tr>
                                    <tr><td style="font-size:20px; padding-top:15px;"><strong>TOTAL:</strong></td> <td align="right" style="font-size:20px; padding-top:15px;"><strong>'.$costoFmt.'</strong></td></tr>
                                </table>
                            </div>
                            <div style="background:#0D09A4; color:#fff; padding:10px; text-align:center; font-size:12px;">
                                REGISTRO ACADÉMICO Y FINANCIERO
                            </div>
                        </div>

                        <p style="font-size:14px; color:#555;">Recuerda que este comprobante es personal e intransferible. Próximamente tu coordinador de sede se pondrá en contacto para indicarte la fecha de inicio de clases.</p>

                        <p style="font-size:13px; color:#718096; margin-top:30px; border-top:1px solid #edf2f7; padding-top:20px;">
                            Atentamente,<br>
                            <strong>Tesorería y Registro - Corisnorte</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#8FBCE6;padding:25px 15px;text-align:center;font-size:12px;color:#0D09A4;">
                       <img src="https://dev.corisnorte.com/assets/img/logo.png"
                       alt="Logo secundario"
                       style="max-height:90px;display:block;margin:0 auto 10px;">
                        © '.date('Y').' '.htmlspecialchars($inst['nombre']).' | Todos los derechos reservados<br>
                        Desarrollado por <a href="https://orbiitech.com" target="_blank" style="color:#0D09A4;font-weight:bold;text-decoration:none;">Orbitech Dynamics</a>
                    </td>
                </tr>
            </table>
        </td></tr>
    </table>
    </body>
    </html>';

    mail($data['correo'], $subject, $message, $headers);
}