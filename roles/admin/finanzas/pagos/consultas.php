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

    // 2. Obtener estudiantes del grupo
    $stmt = $pdo->prepare("SELECT p.id, p.nombres_completos FROM inscripciones i 
                            INNER JOIN personas p ON i.persona_id = p.id 
                            WHERE i.grupo_id = ? AND i.estado = 'En formación'");
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
    
    for($i=1; $i<=$config['duracion']; $i++) {
        $html .= "<th class='text-center' style='background-color:#4a5568 !important; color:white;'>Mes $i</th>";
    }
    $html .= '</tr></thead><tbody>';

    foreach ($estudiantes as $est) {
        // 3. Obtener sumatoria de pagos completados por concepto
        $stmt_p = $pdo->prepare("SELECT concepto, SUM(monto) as total FROM pagos WHERE persona_id = ? AND estado = 'Completado' GROUP BY concepto");
        $stmt_p->execute([$est['id']]);
        $pagos = $stmt_p->fetchAll(PDO::FETCH_KEY_PAIR);

        $html .= "<tr>
                    <td class='ps-3'>
                        <div class='d-flex justify-content-between align-items-center'>
                            <span class='small fw-bold text-uppercase'>".htmlspecialchars($est['nombres_completos'])."</span>
                            <button class='btn btn-link btn-sm p-0 text-primary' onclick='verDetalle({$est['id']})'>
                                <i class='bi bi-eye-fill'></i>
                            </button>
                        </div>
                    </td>";
        
        // --- Renderizado de Cargos Fijos ---
        $html .= renderCelda($pagos['Inscripción'] ?? 0, $config['costo_inscripcion'], $est['id'], 'Inscripción');
        $html .= renderCelda($pagos['Matrícula'] ?? 0, $config['costo_matricula'], $est['id'], 'Matrícula');

        // --- Lógica de Mensualidades en CASCADA ---
        $saldo_disponible = (float)($pagos['Pensión'] ?? 0); 
        $cuota_mensual = (float)$config['mensualidad'];
        $duracion = (int)$config['duracion'];

        for ($i = 1; $i <= $duracion; $i++) {
            $monto_asignado_a_este_mes = 0;

            if ($saldo_disponible >= $cuota_mensual) {
                // El saldo cubre la cuota completa de este mes
                $monto_asignado_a_este_mes = $cuota_mensual;
                $saldo_disponible -= $cuota_mensual;
            } elseif ($saldo_disponible > 0) {
                // El saldo solo cubre una parte (Abono)
                $monto_asignado_a_este_mes = $saldo_disponible;
                $saldo_disponible = 0;
            } else {
                // Ya no hay dinero para los meses siguientes
                $monto_asignado_a_este_mes = 0;
            }

            // Enviamos "Pensión - Mes $i" como sugerencia de concepto al modal
            $html .= renderCelda($monto_asignado_a_este_mes, $cuota_mensual, $est['id'], "Pensión - Mes $i");
        }
        $html .= "</tr>";
    }
    $html .= '</tbody></table>';
    echo $html;
    exit;
}

/**
 * Función para renderizar cada celda de la tabla con su estado (Pagado, Abono, Debe)
 */
function renderCelda($pagado, $costo, $persona_id, $concepto) {
    if ($costo <= 0) return "<td class='text-center text-muted' style='background-color: #f8f9fa;'>-</td>";
    
    $pagado = (float)$pagado;
    $costo = (float)$costo;
    $pendiente = $costo - $pagado;

    $html = "<td class='text-center' style='min-width: 110px;'>";
    
    if ($pagado >= $costo) {
        // ESTADO: PAGADO
        $html .= "<span class='badge bg-success-subtle text-success border border-success-subtle px-2 mb-1' style='font-size: 0.65rem;'>
                    <i class='bi bi-check-circle-fill'></i> PAGADO
                  </span>
                  <div style='font-size: 0.75rem;' class='text-success fw-bold'>$".number_format($pagado, 0)."</div>";
    } else {
        // ESTADO: ABONO O DEBE
        if ($pagado > 0) {
            $html .= "<span class='badge bg-warning-subtle text-dark border border-warning-subtle px-2 mb-1' style='font-size: 0.65rem;'>
                        <i class='bi bi-clock-history'></i> ABONO
                      </span>";
        } else {
            $html .= "<span class='badge bg-danger-subtle text-danger border border-danger-subtle px-2 mb-1' style='font-size: 0.65rem;'>
                        <i class='bi bi-exclamation-triangle'></i> DEBE
                      </span>";
        }
        
        // Mostrar saldo pendiente y botón para pagar
        $html .= "<div class='d-flex flex-column align-items-center mt-1'>
                    <div style='font-size: 0.75rem;' class='text-danger fw-bold'>$".number_format($pendiente, 0)."</div>
                    <button class='btn btn-sm btn-outline-success border-0 p-0 mt-1' 
                            title='Registrar pago para $concepto' 
                            onclick=\"abrirModalPago({$persona_id}, '{$concepto}', {$pendiente})\">
                        <i class='bi bi-cash-stack fs-5'></i>
                    </button>
                  </div>";
    }
    
    $html .= "</td>";
    return $html;
}