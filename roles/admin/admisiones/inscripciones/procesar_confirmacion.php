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

        // 1. Obtener toda la información necesaria para los correos (Agregamos costo_matricula)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sqlInfo = "
            SELECT 
                p.nombres_completos, 
                p.correo, 
                i.codigo,
                prog.nombre AS programa,
                prog.costo_matricula,
                g.nombre AS grupo,
                g.jornada,
                s.nombre AS sede
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

        // 2. Actualizar el estado a 'Inscrito' en la base de datos
        $sqlUpd = "UPDATE inscripciones SET estado = 'Inscrito' WHERE id IN ($placeholders)";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute($ids);

        // 3. Preparar y enviar correos individuales
        $enviadosCount = 0;
        
        foreach ($estudiantes as $data) {
            $correoDestino = trim($data['correo'] ?? '');

            if (!empty($correoDestino) && filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
                
                $subject = "Inscripción Confirmada - {$data['programa']}";
                $codigo  = $data['codigo'];
                // Formateamos el costo (ej: $ 1.200.000)
                $costoFmt = "$ " . number_format($data['costo_matricula'], 0, ',', '.');

                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Admisiones Corisnorte <admisiones@corisnorte.com>\r\n";
                $headers .= "Reply-To: admisiones@corisnorte.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                $message = '<!DOCTYPE html>
                <html lang="es">
                <head><meta charset="UTF-8"></head>
                <body style="margin:0;padding:0;background-color:#f2f4f7;font-family:Arial,Helvetica,sans-serif;">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 10px;">
                <tr><td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;">
                        <tr>
                            <td style="background:#8FBCE6;padding:25px 20px;text-align:center;">
                                <img src="https://dev.corisnorte.com/assets/img/logo-corisnorte-text.png" alt="CORISNORTE" style="max-height:65px;display:block;margin:0 auto;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:35px 30px;color:#333333;">
                                <h2 style="margin:0 0 10px;color:#FC0000;text-align:center;">¡Inscripción Confirmada!</h2>
                                <p style="font-size:15px;line-height:1.6;">
                                    Hola <strong>'.htmlspecialchars($data['nombres_completos']).'</strong>, nos complace informarte que tu inscripción ha sido <strong>confirmada exitosamente</strong>.
                                </p>
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px auto;border-collapse:collapse;max-width:480px;">
                                    <tr>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;"><strong>Código</strong></td>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;color:#FC0000;font-weight:bold;">'.$codigo.'</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;">Programa</td>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;">'.$data['programa'].'</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;">Sede / Grupo</td>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;">'.$data['sede'].' - '.$data['grupo'].'</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0;"><strong>Costo Matrícula</strong></td>
                                        <td style="padding:10px;border-bottom:1px solid #e0e0e0; font-weight:bold;">'.$costoFmt.'</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:10px;"><strong>Nuevo Estado</strong></td>
                                        <td style="padding:10px;"><span style="color:#0D09A4;font-weight:bold;">INSCRITO</span></td>
                                    </tr>
                                </table>
                                <p style="font-size:14px;line-height:1.6;text-align:center;background:#f9f9f9;padding:15px;border-radius:5px;">
                                    <strong>¡Estás a un paso de iniciar!</strong><br>
                                    Próximamente recibirás las instrucciones para formalizar tu proceso de matrícula y el inicio de clases.
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

                // Envío nativo
                mail($correoDestino, $subject, $message, $headers, "-f admisiones@corisnorte.com");
                $enviadosCount++;
            }
        }

        $pdo->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => "Se confirmaron $enviadosCount inscripciones y se enviaron los correos respectivos."
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida.']);
}