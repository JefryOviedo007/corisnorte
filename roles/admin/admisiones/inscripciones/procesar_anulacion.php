<?php
session_start();
require_once __DIR__ . "/../../../../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    
    $ids = json_decode($_POST['ids']);
    
    if (empty($ids)) {
        echo json_encode(['status' => 'error', 'message' => 'No hay registros para procesar.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Obtenemos info para el correo
        $sqlInfo = "
            SELECT 
                p.nombres_completos, p.correo, i.codigo,
                prog.nombre AS programa,
                s.nombre AS sede, s.telefono AS sede_tel
            FROM inscripciones i
            INNER JOIN personas p ON i.persona_id = p.id
            INNER JOIN grupos g ON i.grupo_id = g.id
            INNER JOIN programas prog ON g.programa_id = prog.id
            INNER JOIN sedes s ON g.sede_id = s.id
            WHERE i.id IN ($placeholders)
        ";
        
        $stmtInfo = $pdo->prepare($sqlInfo);
        $stmtInfo->execute($ids);
        $estudiantes = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

        // 2. Actualizar estado a 'Anulado'
        $sqlUpd = "UPDATE inscripciones SET estado = 'Anulado' WHERE id IN ($placeholders)";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute($ids);

        // 3. Envío de correos
        $enviadosCount = 0;
        foreach ($estudiantes as $data) {
            $correoDestino = trim($data['correo'] ?? '');

            if (!empty($correoDestino) && filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
                
                $subject = "Notificación de Anulación de Inscripción - " . $data['programa'];
                
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Admisiones Corisnorte <admisiones@corisnorte.com>\r\n";
                $headers .= "Reply-To: admisiones@corisnorte.com\r\n";

                $message = '
                <html>
                <body style="margin:0;padding:0;background-color:#f8f9fa;font-family:Arial,sans-serif;">
                <table width="100%" bgcolor="#f8f9fa" style="padding:20px;">
                    <tr><td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;">
                            <tr>
                                <td style="background:#8FBCE6;padding:25px;text-align:center;">
                                    <img src="https://dev.corisnorte.com/assets/img/logo-corisnorte-text.png" style="max-height:60px;">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:35px;color:#333;">
                                    <h2 style="color:#FC0000; text-align:center;">Inscripción Anulada</h2>
                                    
                                    <p>Hola <strong>'.htmlspecialchars($data['nombres_completos']).'</strong>,</p>
                                    <p>Te informamos que tu proceso de inscripción con el código <strong>'.$data['codigo'].'</strong> para el programa <strong>'.htmlspecialchars($data['programa']).'</strong> ha sido <strong>ANULADO</strong>.</p>
                                    
                                    <div style="background:#fff5f5; border:1px solid #feb2b2; padding:20px; border-radius:8px; margin:25px 0; color:#c53030;">
                                        <p style="margin:0;">Esta decisión implica que tu cupo ha sido liberado y el proceso administrativo se ha dado por finalizado.</p>
                                    </div>

                                    <p>Si consideras que esto es un error o deseas iniciar un nuevo proceso, por favor comunícate con tu sede:</p>
                                    <p style="margin:5px 0;"><strong>Sede:</strong> '.htmlspecialchars($data['sede']).'</p>
                                    <p style="margin:5px 0;"><strong>Teléfono:</strong> '.htmlspecialchars($data['sede_tel']).'</p>

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
        echo json_encode(['status' => 'success', 'message' => "Se han anulado $enviadosCount registros exitosamente."]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}