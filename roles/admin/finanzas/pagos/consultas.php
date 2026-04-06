<?php
session_start();
require_once "../../../../config.php";

$action = $_GET['action'] ?? '';

if ($action == 'getSedes') {
    $stmt = $pdo->query("SELECT id, nombre FROM sedes ORDER BY nombre");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action == 'getGrupos') {
    $sede_id = $_GET['sede_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT g.id, g.nombre, p.nombre as programa FROM grupos g 
                            INNER JOIN programas p ON g.programa_id = p.id WHERE g.sede_id = ?");
    $stmt->execute([$sede_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action == 'getTabla') {
    $grupo_id = $_GET['grupo_id'] ?? 0;
    
    // 1. Obtener configuración del programa (costos y duración)
    $stmt = $pdo->prepare("SELECT p.* FROM programas p JOIN grupos g ON g.programa_id = p.id WHERE g.id = ?");
    $stmt->execute([$grupo_id]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener estudiantes del grupo — incluimos descuento
    $stmt = $pdo->prepare("SELECT p.id, p.nombres_completos, p.descuento 
                            FROM inscripciones i 
                            INNER JOIN personas p ON i.persona_id = p.id 
                            WHERE i.grupo_id = ? AND i.estado = 'En formación'
                            ORDER BY p.nombres_completos ASC");
    $stmt->execute([$grupo_id]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$estudiantes) {
        echo "<div class='alert alert-info border-0 shadow-sm'>No hay estudiantes registrados en este grupo.</div>";
        exit;
    }

    // Cabecera de la tabla
    $html = '<table class="table table-hover align-middle">';
    $html .= '<thead>
                <tr>
                    <th rowspan="2" class="text-center">Estudiante</th>
                    <th colspan="2" class="text-center">Cargos Administrativos</th>
                    <th colspan="'.$config['duracion'].'" class="text-center">Control de Mensualidades</th>
                </tr>
                <tr>
                    <th class="text-center" style="background-color:#4a5568 !important; color:white;">Inscripción</th>
                    <th class="text-center" style="background-color:#4a5568 !important; color:white;">Matrícula</th>';
    
    for ($i = 1; $i <= $config['duracion']; $i++) {
        $html .= "<th class='text-center' style='background-color:#4a5568 !important; color:white;'>Mes $i</th>";
    }
    $html .= '</tr></thead><tbody>';

    foreach ($estudiantes as $est) {
        // 3. Obtener sumatoria de pagos completados por concepto
        $stmt_p = $pdo->prepare("SELECT concepto, SUM(monto) as total 
                                  FROM pagos 
                                  WHERE persona_id = ? AND estado = 'Completado' 
                                  GROUP BY concepto");
        $stmt_p->execute([$est['id']]);
        $pagos = $stmt_p->fetchAll(PDO::FETCH_KEY_PAIR);

        // 4. Calcular descuento del estudiante (solo aplica a Pensión)
        $descuento     = (int)($est['descuento'] ?? 0);
        $cuota_mensual = (float)$config['mensualidad'];
        $duracion      = (int)$config['duracion'];

        // Cuota real con descuento aplicado
        $cuota_con_desc = ($descuento > 0)
            ? round($cuota_mensual * (1 - $descuento / 100))
            : $cuota_mensual;

        // 5. Celda de nombre del estudiante con badge de descuento si aplica
        $badge_desc = ($descuento > 0)
            ? "<span class='badge bg-warning text-dark ms-1' title='Descuento aplicado en pensión'>
                   <i class='bi bi-percent'></i> {$descuento}%
               </span>"
            : "";

        $html .= "<tr>
                    <td class='ps-3'>
                        <div class='d-flex justify-content-between align-items-center'>
                            <div>
                                <span class='small fw-bold text-uppercase'>".htmlspecialchars($est['nombres_completos'])."</span>
                                {$badge_desc}
                            </div>
                            <button class='btn btn-link btn-sm p-0 text-primary' onclick='verDetalle({$est['id']})'>
                                <i class='bi bi-eye-fill'></i>
                            </button>
                        </div>
                    </td>";
        
        // 6. Cargos fijos — sin descuento
        $html .= renderCelda($pagos['Inscripción'] ?? 0, $config['costo_inscripcion'], $est['id'], 'Inscripción');
        $html .= renderCelda($pagos['Matrícula']   ?? 0, $config['costo_matricula'],   $est['id'], 'Matrícula');

        // 7. Mensualidades en cascada con cuota descontada
        $saldo_disponible = (float)($pagos['Pensión'] ?? 0);

        for ($i = 1; $i <= $duracion; $i++) {
            $monto_asignado = 0;

            if ($saldo_disponible >= $cuota_con_desc) {
                $monto_asignado    = $cuota_con_desc;
                $saldo_disponible -= $cuota_con_desc;
            } elseif ($saldo_disponible > 0) {
                $monto_asignado   = $saldo_disponible;
                $saldo_disponible = 0;
            }

            $html .= renderCelda($monto_asignado, $cuota_con_desc, $est['id'], "Pensión - Mes $i", $descuento);
        }

        $html .= "</tr>";
    }

    $html .= '</tbody></table>';
    echo $html;
    exit;
}

/**
 * Renderiza cada celda con su estado (Pagado, Abono, Debe)
 * $descuento: porcentaje aplicado (solo informativo para mostrar badge en pensiones)
 */
function renderCelda($pagado, $costo, $persona_id, $concepto, $descuento = 0) {
    if ($costo <= 0) return "<td class='text-center text-muted' style='background-color:#f8f9fa;'>-</td>";

    $pagado    = (float)$pagado;
    $costo     = (float)$costo;
    $pendiente = $costo - $pagado;

    $html = "<td class='text-center' style='min-width:110px;'>";

    // Badge de descuento solo en pensiones
    $esPension = stripos($concepto, 'Pensi') !== false || stripos($concepto, 'Mes') !== false;
    if ($esPension && $descuento > 0) {
        $html .= "<span class='badge bg-warning-subtle text-warning border border-warning px-1 mb-1' style='font-size:0.6rem;'>
                    <i class='bi bi-percent'></i> -{$descuento}%
                  </span><br>";
    }

    if ($pagado >= $costo) {
        // PAGADO
        $html .= "<span class='badge bg-success-subtle text-success border border-success-subtle px-2 mb-1' style='font-size:0.65rem;'>
                    <i class='bi bi-check-circle-fill'></i> PAGADO
                  </span>
                  <div style='font-size:0.75rem;' class='text-success fw-bold'>
                    $".number_format($pagado, 0, ',', '.')."
                  </div>";
    } else {
        // ABONO o DEBE
        if ($pagado > 0) {
            $html .= "<span class='badge bg-warning-subtle text-dark border border-warning-subtle px-2 mb-1' style='font-size:0.65rem;'>
                        <i class='bi bi-clock-history'></i> ABONO
                      </span>";
        } else {
            $html .= "<span class='badge bg-danger-subtle text-danger border border-danger-subtle px-2 mb-1' style='font-size:0.65rem;'>
                        <i class='bi bi-exclamation-triangle'></i> DEBE
                      </span>";
        }

        $html .= "<div class='d-flex flex-column align-items-center mt-1'>
                    <div style='font-size:0.75rem;' class='text-danger fw-bold'>
                        $".number_format($pendiente, 0, ',', '.')."
                    </div>
                    <button class='btn btn-sm btn-outline-success border-0 p-0 mt-1'
                            title='Registrar pago: {$concepto}'
                            onclick=\"abrirModalPago({$persona_id}, '{$concepto}', {$pendiente})\">
                        <i class='bi bi-cash-stack fs-5'></i>
                    </button>
                  </div>";
    }

    $html .= "</td>";
    return $html;
}