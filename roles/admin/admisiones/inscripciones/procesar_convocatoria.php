<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    
    $ids = json_decode($_POST['ids']);
    
    if (empty($ids)) {
        echo json_encode(['status' => 'error', 'message' => 'No se seleccionaron registros.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Consulta extendida con costo_matricula y datos de sede
        $sqlInfo = "
            SELECT 
                p.nombres_completos, p.correo, i.codigo,
                prog.nombre AS programa,
                prog.costo_matricula,
                s.nombre AS sede, s.direccion AS sede_dir, s.telefono AS sede_tel, s.ciudad AS sede_ciudad,
                s.coordinador_nombre, s.coordinador_telefono, s.coordinador_correo
            FROM inscripciones i
            INNER JOIN personas p ON i.persona_id = p.id
            INNER JOIN grupos g ON i.grupo_id = g.id
            INNER JOIN programas prog ON g.programa_id = prog.id
            INNER JOIN sedes s ON g.sede_id = s.id
            WHERE i.id IN ($placeholders) AND i.estado = 'Inscrito'
        ";
        
        $stmtInfo = $pdo->prepare($sqlInfo);
        $stmtInfo->execute($ids);
        $estudiantes = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

        if (empty($estudiantes)) {
            echo json_encode(['status' => 'error', 'message' => 'No hay registros válidos para convocar.']);
            $pdo->rollBack();
            exit;
        }

        // 2. Actualizar estado a 'Convocado'
        $sqlUpd = "UPDATE inscripciones SET estado = 'Convocado' WHERE id IN ($placeholders) AND estado = 'Inscrito'";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute($ids);

        // 3. Envío de correos
        $enviadosCount = 0;
        foreach ($estudiantes as $data) {
            $correoDestino = trim($data['correo'] ?? '');

            if (!empty($correoDestino) && filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
                
                $subject = "Citación a Matrícula Académica - " . $data['programa'];
                $costoFmt = "$ " . number_format($data['costo_matricula'], 0, ',', '.');
                
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Admisiones Corisnorte <admisiones@corisnorte.com>\r\n";
                $headers .= "Reply-To: admisiones@corisnorte.com\r\n";

                $message = '
                <html>
                <body style="margin:0;padding:0;background-color:#f4f7f9;font-family:Arial,sans-serif;">
                <table width="100%" bgcolor="#f4f7f9" style="padding:20px;">
                    <tr><td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;">
                            <tr>
                                <td style="background:#8FBCE6;padding:25px 20px;text-align:center;">
                                    <img src="https://dev.corisnorte.com/assets/img/logo-corisnorte-text.png" alt="CORISNORTE" style="max-height:65px;display:block;margin:0 auto;">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:35px;color:#333;">
                                    <h2 style="color:#0D09A4; text-align:center; margin-bottom:25px;">¡Es hora de matricularte!</h2>
                                    
                                    <p>Estimado(a) <strong>'.htmlspecialchars($data['nombres_completos']).'</strong>,</p>
                                    <p>Te informamos que has sido seleccionado para formalizar tu <strong>Matrícula Académica</strong> en el programa de <strong>'.htmlspecialchars($data['programa']).'</strong>.</p>
                                    
                                    <div style="text-align:center; background:#eef2ff; border:2px dashed #0D09A4; padding:20px; border-radius:10px; margin:25px 0;">
                                        <span style="color:#0D09A4; font-size:14px; text-transform:uppercase; font-weight:bold;">Valor de la Matrícula</span><br>
                                        <span style="color:#000; font-size:32px; font-weight:bold;">'.$costoFmt.'</span>
                                    </div>

                                    <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:25px;">
                                        <h4 style="margin-top:0; color:#0D09A4; border-bottom:1px solid #ddd; padding-bottom:10px;">Documentos Necesarios:</h4>
                                        <ul style="line-height:1.8; color:#444;">
                                            <li>Fotocopia del documento de identidad ampliada al 150%.</li>
                                            <li>Fotocopia del Acta de Grado y Diploma de Bachiller.</li>
                                            <li>Resultado de las pruebas saber 11 (ICFES).</li>
                                            <li>Certificado de afiliación a EPS o SISBÉN actualizado.</li>
                                            <li>Dos (2) fotos tamaño 3x4 fondo azul o blanco.</li>
                                            <li>Soporte original de pago de matrícula.</li>
                                        </ul>
                                    </div>

                                    <div style="background:#eef2ff; padding:20px; border-radius:8px; margin-bottom:25px;">
                                        <h4 style="margin-top:0; color:#0D09A4;">Lugar de Atención:</h4>
                                        <p style="margin:5px 0;"><strong>Sede:</strong> '.htmlspecialchars($data['sede']).'</p>
                                        <p style="margin:5px 0;"><strong>Dirección:</strong> '.htmlspecialchars($data['sede_dir']).' ('.htmlspecialchars($data['sede_ciudad']).')</p>
                                        <p style="margin:5px 0;"><strong>Teléfono Sede:</strong> '.htmlspecialchars($data['sede_tel']).'</p>
                                    </div>

                                    <div style="border-left:4px solid #0D09A4; padding-left:15px; margin:20px 0; font-size:14px;">
                                        <p style="margin:5px 0; color:#555;"><strong>Coordinador de Sede:</strong> '.htmlspecialchars($data['coordinador_nombre']).'</p>
                                        <p style="margin:5px 0; color:#555;"><strong>Contacto:</strong> '.htmlspecialchars($data['coordinador_telefono']).' | '.htmlspecialchars($data['coordinador_correo']).'</p>
                                    </div>

                                    <p style="font-size:15px; font-weight:bold; color:#0D09A4; text-align:center; margin-top:30px;">
                                        Por favor, acércate a la sede mencionada con los documentos necesarios para finalizar tu vinculación exitosamente.
                                    </p>
                                    <p style="font-size:13px; color:#718096; margin-top:30px; border-top:1px solid #edf2f7; padding-top:20px;">
                                        Atentamente,<br>
                                        <strong>Departamento de Admisiones - Corisnorte</strong>
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

                mail($correoDestino, $subject, $message, $headers, "-f admisiones@corisnorte.com");
                $enviadosCount++;
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => "Se han convocado $enviadosCount estudiantes con éxito."]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}