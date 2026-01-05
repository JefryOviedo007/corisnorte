<?php
require_once 'config.php';
header('Content-Type: application/json');

$persona_id = intval($_POST['persona_id'] ?? 0);
$grupo_id   = intval($_POST['grupo_id'] ?? 0);

if (!$persona_id || !$grupo_id) {
  echo json_encode([
    'success' => false,
    'message' => 'Datos incompletos'
  ]);
  exit;
}

try {
  $pdo->beginTransaction();

  /* 1️⃣ VERIFICAR GRUPO Y CUPOS */
  $stmt = $pdo->prepare("
    SELECT cupos_disponibles
    FROM grupos
    WHERE id = ?
    FOR UPDATE
  ");
  $stmt->execute([$grupo_id]);
  $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$grupo) {
    throw new Exception('Grupo no encontrado');
  }

  if ($grupo['cupos_disponibles'] <= 0) {
    echo json_encode([
      'success' => false,
      'type' => 'cupos',
      'message' => 'No hay cupos disponibles para este programa.'
    ]);
    $pdo->rollBack();
    exit;
  }

  /* 2️⃣ VALIDAR INSCRIPCIÓN DUPLICADA */
  $stmt = $pdo->prepare("
    SELECT id
    FROM inscripciones
    WHERE persona_id = ?
    AND grupo_id = ?
    AND estado != 'Anulado'
    LIMIT 1
  ");
  $stmt->execute([$persona_id, $grupo_id]);

  if ($stmt->fetch()) {
    echo json_encode([
      'success' => false,
      'type' => 'duplicado',
      'message' => 'Ya tienes una inscripción activa en este programa.'
    ]);
    $pdo->rollBack();
    exit;
  }

  /* 3️⃣ GENERAR CÓDIGO */
  $year = date('Y');
  $stmt = $pdo->query("SELECT COUNT(*) FROM inscripciones");
  $consecutivo = str_pad($stmt->fetchColumn() + 1, 6, '0', STR_PAD_LEFT);
  $codigo = "INS-$grupo_id-$consecutivo";

  /* 4️⃣ INSERTAR INSCRIPCIÓN */
  $stmt = $pdo->prepare("
    INSERT INTO inscripciones
    (codigo, persona_id, grupo_id, estado)
    VALUES (?,?,?, 'Preinscrito')
  ");
  $stmt->execute([$codigo, $persona_id, $grupo_id]);

  /* 5️⃣ DESCONTAR CUPO */
  $stmt = $pdo->prepare("
    UPDATE grupos
    SET cupos_disponibles = cupos_disponibles - 1
    WHERE id = ?
  ");
  $stmt->execute([$grupo_id]);

  /* 6️⃣ OBTENER DATOS PARA CORREO */
  $stmt = $pdo->prepare("
    SELECT
      p.nombres_completos,
      p.correo,
      pr.nombre AS programa,
      g.nombre AS grupo,
      g.jornada,
      s.nombre AS sede,
      s.ciudad
    FROM personas p
    JOIN inscripciones i ON i.persona_id = p.id
    JOIN grupos g ON g.id = i.grupo_id
    JOIN programas pr ON pr.id = g.programa_id
    JOIN sedes s ON s.id = g.sede_id
    WHERE i.codigo = ?
  ");
  $stmt->execute([$codigo]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  /* 7️⃣ ENVIAR CORREO (MAIL NATIVO CORRECTO) */
  if ($data) {

    $correo = trim($data['correo'] ?? '');

    if (!empty($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL)) {

      $subject = "Confirmación de Preinscripción - {$data['programa']}";

      $headers  = "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
      $headers .= "From: Admisiones Corisnorte <admisiones@corisnorte.com>\r\n";
      $headers .= "Reply-To: admisiones@corisnorte.com\r\n";
      $headers .= "X-Mailer: PHP/" . phpversion();

      $message = '<!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <title>Confirmación de Preinscripción</title>
        </head>
        
        <body style="margin:0;padding:0;background-color:#f2f4f7;font-family:Arial,Helvetica,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 10px;">
        <tr>
        <td align="center">
        
        <!-- CONTENEDOR -->
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;">
        
          <!-- HEADER -->
          <tr>
            <td style="background:#8FBCE6;padding:25px 20px;text-align:center;">
              <img src="https://dev.corisnorte.com/assets/img/logo-corisnorte-text.png"
                   alt="CORISNORTE"
                   style="max-height:65px;display:block;margin:0 auto;">
            </td>
          </tr>
        
          <!-- BODY -->
          <tr>
            <td style="padding:35px 30px;color:#333333;">
              
              <h2 style="margin:0 0 10px;color:#FC0000;text-align:center;">
                Confirmación de Preinscripción
              </h2>
        
              <p style="font-size:15px;line-height:1.6;">
                Hola <strong>'.htmlspecialchars($data['nombres_completos']).'</strong>,
              </p>
        
              <p style="font-size:15px;line-height:1.6;">
                Hemos recibido correctamente tu <strong>preinscripción</strong>.
                A continuación encontrarás el resumen de tu proceso:
              </p>
        
              <!-- TABLA RESUMEN -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px auto;border-collapse:collapse;max-width:480px;">
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;"><strong>Código</strong></td>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;color:#FC0000;font-weight:bold;">
                    '.$codigo.'
                  </td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">Programa</td>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">'.$data['programa'].'</td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">Grupo</td>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">'.$data['grupo'].'</td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">Jornada</td>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">'.$data['jornada'].'</td>
                </tr>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">Sede</td>
                  <td style="padding:10px;border-bottom:1px solid #e0e0e0;">
                    '.$data['sede'].' - '.$data['ciudad'].'
                  </td>
                </tr>
                <tr>
                  <td style="padding:10px;"><strong>Estado</strong></td>
                  <td style="padding:10px;">
                    <span style="color:#0D09A4;font-weight:bold;">PREINSCRITO</span>
                  </td>
                </tr>
              </table>
        
              <p style="font-size:14px;line-height:1.6;">
                Próximamente serás contactado por correo electrónico para la confirmación de tu inscripción al programa seleccionado.
              </p>
        
              <p style="font-size:13px;color:#666666;">
                Conserva este correo y tu código de inscripción para futuras consultas.
              </p>
        
            </td>
          </tr>
        
          <!-- FOOTER -->
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
        
        </td>
        </tr>
        </table>
        </body>
        </html>';


      // 🔑 CLAVE: envelope sender (-f)
      $enviado = mail(
        $correo,
        $subject,
        $message,
        $headers,
        "-f admisiones@corisnorte.com"
      );

      if (!$enviado) {
        error_log("❌ MAIL(): Falló envío a {$correo} | Código {$codigo}");
      } else {
        error_log("✅ MAIL(): Enviado correctamente a {$correo} | Código {$codigo}");
      }

    } else {
      error_log("⚠️ Correo inválido: '{$correo}' | persona_id {$persona_id}");
    }
  }

  $pdo->commit();

  echo json_encode([
    'success' => true,
    'codigo' => $codigo
  ]);

} catch (Exception $e) {
  $pdo->rollBack();
  error_log("❌ ERROR INSCRIPCIÓN: " . $e->getMessage());

  echo json_encode([
    'success' => false,
    'message' => 'Error interno al procesar la inscripción'
  ]);
}
