<?php
session_start();
require_once "../../../../config.php";

$action = $_GET['action'] ?? '';

// ... (getSedes y getGrupos se mantienen igual) ...

if ($action == 'getSedes') {
    $stmt = $pdo->query("SELECT id, nombre FROM sedes ORDER BY nombre");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action == 'getGrupos') {
    $sede_id = $_GET['sede_id'];
    $stmt = $pdo->prepare("SELECT g.id, g.nombre, p.nombre as programa FROM grupos g 
                            INNER JOIN programas p ON g.programa_id = p.id WHERE g.sede_id = ?");
    $stmt->execute([$sede_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action == 'getTabla') {
    $grupo_id = $_GET['grupo_id'];
    
    $stmt = $pdo->prepare("SELECT p.* FROM programas p JOIN grupos g ON g.programa_id = p.id WHERE g.id = ?");
    $stmt->execute([$grupo_id]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT p.id, p.nombres_completos FROM inscripciones i 
                            INNER JOIN personas p ON i.persona_id = p.id 
                            WHERE i.grupo_id = ? AND i.estado = 'En formación'");
    $stmt->execute([$grupo_id]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$estudiantes) {
        echo "<div class='alert alert-info border-0 shadow-sm'>No hay estudiantes registrados en este grupo.</div>";
        exit;
    }

    $html = '<table class="table table-hover align-middle">';
    $html .= '<thead>
                <tr>
                    <th rowspan="2" class="text-center">Estudiante</th>
                    <th colspan="2" class="text-center">Cargos Administrativos</th>
                    <th colspan="'.$config['duracion'].'" class="text-center">Control de Mensualidades</th>
                </tr>
                <tr>
                    <th class="text-center" style="background-color:#4a5568 !important">Inscripción</th>
                    <th class="text-center" style="background-color:#4a5568 !important">Matrícula</th>';
    for($i=1; $i<=$config['duracion']; $i++) $html .= "<th class='text-center' style='background-color:#4a5568 !important'>Mes $i</th>";
    $html .= '</tr></thead><tbody>';

    foreach ($estudiantes as $est) {
        // Obtenemos los pagos agrupados
        $stmt_p = $pdo->prepare("SELECT concepto, SUM(monto) as total FROM pagos WHERE persona_id = ? AND estado = 'Completado' GROUP BY concepto");
        $stmt_p->execute([$est['id']]);
        $pagos_raw = $stmt_p->fetchAll(PDO::FETCH_KEY_PAIR);

        // NORMALIZACIÓN DE CLAVES: Quitamos tildes para evitar errores de lectura en el array
        $pagos = [];
        foreach($pagos_raw as $key => $val) {
            // Convertimos 'Pensión' a 'Pension' o simplemente manejamos la tilde con cuidado
            $pagos[$key] = $val;
        }
        
        $html .= "<tr>
                    <td class='ps-3'>
                        <div class='d-flex justify-content-between align-items-center'>
                            <span class='small fw-bold text-uppercase'>".htmlspecialchars($est['nombres_completos'])."</span>
                            <button class='btn btn-link btn-sm p-0 text-primary' onclick='verDetalle({$est['id']})'>
                                <i class='bi bi-eye-fill'></i>
                            </button>
                        </div>
                    </td>";
        
        // Cargos Administrativos
        $html .= renderCelda($pagos['Inscripción'] ?? 0, $config['costo_inscripcion'], $est['id'], 'Inscripción');
        $html .= renderCelda($pagos['Matrícula'] ?? 0, $config['costo_matricula'], $est['id'], 'Matrícula');

        // Lógica de Mensualidades (Aquí estaba el fallo)
        // Buscamos 'Pensión' (con tilde, tal cual está en tu ENUM)
        $saldo_p = (float)($pagos['Pensión'] ?? 0); 
        $cuota = (float)$config['mensualidad'];

        for ($i=1; $i<=$config['duracion']; $i++) {
            $monto_este_mes = 0;
            if ($saldo_p >= $cuota) { 
                $monto_este_mes = $cuota; 
                $saldo_p -= $cuota; 
            } elseif ($saldo_p > 0) { 
                $monto_este_mes = $saldo_p; 
                $saldo_p = 0; 
            }
            
            // Importante: El concepto que enviamos al modal de pago debe ser "Mes $i" 
            // para que en el backend se guarde como 'Pensión' pero sepamos qué mes es en la referencia.
            $html .= renderCelda($monto_este_mes, $cuota, $est['id'], "Pensión - Mes $i");
        }
        $html .= "</tr>";
    }
    $html .= '</tbody></table>';
    echo $html;
    exit;
}

function renderCelda($pagado, $costo, $persona_id, $concepto) {
    if ($costo <= 0) return "<td class='text-center text-muted'>-</td>";
    
    $pagado = (float)$pagado;
    $costo = (float)$costo;
    $pendiente = $costo - $pagado;

    $html = "<td class='text-center' style='min-width: 100px;'>";
    
    if ($pagado >= $costo) {
        // PAGADO TOTAL
        $html .= "<span class='badge bg-success-subtle text-success border border-success-subtle px-2 mb-1'>
                    <i class='bi bi-check-circle-fill'></i> PAGADO
                  </span>
                  <div style='font-size: 0.7rem;' class='text-success fw-bold'>$".number_format($pagado, 0)."</div>";
    } else {
        // DEBE O ABONO
        if ($pagado > 0) {
            $html .= "<span class='badge bg-warning-subtle text-dark border border-warning-subtle px-2'>
                        <i class='bi bi-clock-history'></i> ABONO
                      </span>";
        } else {
            $html .= "<span class='badge bg-danger-subtle text-danger border border-danger-subtle px-2'>
                        <i class='bi bi-exclamation-triangle'></i> DEBE
                      </span>";
        }
        
        // Valor pendiente y botón de pago
        $html .= "<div class='d-flex flex-column align-items-center'>
                    <div style='font-size: 0.7rem;' class='text-danger fw-bold'>$".number_format($pendiente, 0)."
                        <button class='btn btn-sm btn-outline-success border-0 p-0' 
                                title='Pagar saldo' 
                                onclick=\"abrirModalPago({$persona_id}, '{$concepto}', {$pendiente})\">
                            <i class='bi bi-cash-stack fs-5'></i>
                        </button>
                    </div>
                  </div>";
    }
    
    $html .= "</td>";
    return $html;
}