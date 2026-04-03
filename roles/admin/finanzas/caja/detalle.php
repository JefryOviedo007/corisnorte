<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . "/../../../../config.php";

$tipo         = $_GET['tipo']         ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
$sede_id_get  = !empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
$rol          = $_SESSION['rol'] ?? '';
$sede_id_ses  = $_SESSION['sede_id'] ?? null;

$sede_activa = $sede_id_get ?? ($rol !== 'Admin' ? $sede_id_ses : null);

// Construir WHERE de sede
$in_usuarios = '0';
$in_inscrip  = '0';

if ($sede_activa) {
    $s = $pdo->prepare("SELECT id FROM usuarios WHERE sede_id = ?");
    $s->execute([$sede_activa]);
    $uids = $s->fetchAll(PDO::FETCH_COLUMN);
    $in_usuarios = !empty($uids) ? implode(',', $uids) : '0';

    $s2 = $pdo->prepare("SELECT i.id FROM inscripciones i INNER JOIN grupos g ON i.grupo_id = g.id WHERE g.sede_id = ?");
    $s2->execute([$sede_activa]);
    $iids = $s2->fetchAll(PDO::FETCH_COLUMN);
    $in_inscrip = !empty($iids) ? implode(',', $iids) : '0';
}

$where_pag = $sede_activa ? "AND p.inscripcion_id IN ($in_inscrip)" : "";
$where_ing = $sede_activa ? "AND i.usuario_id IN ($in_usuarios)"    : "";
$where_egr = $sede_activa ? "AND e.usuario_id IN ($in_usuarios)"    : "";

function tablaRegistros(array $filas, string $tipo): string {
    if (empty($filas)) {
        return '<p class="text-center text-muted py-3 small">No hay registros en este periodo.</p>';
    }

    $html = '<table class="table table-sm table-hover align-middle mb-0">';

    if ($tipo === 'pagos') {
        $html .= '<thead class="table-light"><tr>
            <th>Fecha</th><th>Estudiante</th><th>Concepto</th>
            <th class="text-end">Efectivo</th><th class="text-end">Transfer.</th><th class="text-end">Total</th>
        </tr></thead><tbody>';
        foreach ($filas as $r) {
            $html .= "<tr>
                <td class='small'>" . date('d/m/Y', strtotime($r['fecha_pago'])) . "</td>
                <td class='small'>" . htmlspecialchars(mb_convert_case($r['persona'] ?? '—', MB_CASE_TITLE, 'UTF-8')) . "</td>
                <td><span class='badge bg-light text-dark border'>{$r['concepto']}</span></td>
                <td class='text-end small'>$" . number_format($r['monto_efectivo'], 0, ',', '.') . "</td>
                <td class='text-end small'>$" . number_format($r['monto_transferencia'], 0, ',', '.') . "</td>
                <td class='text-end fw-bold text-success'>$" . number_format($r['monto'], 0, ',', '.') . "</td>
            </tr>";
        }
    } elseif ($tipo === 'otros_ingresos' || $tipo === 'ingresos') {
        $html .= '<thead class="table-light"><tr>
            <th>Fecha</th><th>Concepto</th><th>Cliente</th>
            <th class="text-end">Efectivo</th><th class="text-end">Transfer.</th><th class="text-end">Total</th>
        </tr></thead><tbody>';
        foreach ($filas as $r) {
            $nombre = $r['cliente'] ?? $r['observaciones'] ?? '—';
            $html .= "<tr>
                <td class='small'>" . date('d/m/Y', strtotime($r['fecha_ingreso'])) . "</td>
                <td class='small fw-semibold'>" . htmlspecialchars($r['concepto']) . "</td>
                <td class='small'>" . htmlspecialchars(mb_convert_case(trim($nombre), MB_CASE_TITLE, 'UTF-8')) . "</td>
                <td class='text-end small'>$" . number_format($r['monto_efectivo'], 0, ',', '.') . "</td>
                <td class='text-end small'>$" . number_format($r['monto_transferencia'], 0, ',', '.') . "</td>
                <td class='text-end fw-bold text-success'>$" . number_format($r['monto'], 0, ',', '.') . "</td>
            </tr>";
        }
    } else {
        $html .= '<thead class="table-light"><tr>
            <th>Fecha</th><th>Concepto</th><th>Pagado a</th>
            <th class="text-end">Efectivo</th><th class="text-end">Transfer.</th><th class="text-end">Total</th>
        </tr></thead><tbody>';
        foreach ($filas as $r) {
            $nombre = $r['proveedor'] ?? $r['observaciones'] ?? '—';
            $html .= "<tr>
                <td class='small'>" . date('d/m/Y', strtotime($r['fecha_egreso'])) . "</td>
                <td class='small fw-semibold'>" . htmlspecialchars($r['concepto']) . "</td>
                <td class='small'>" . htmlspecialchars(mb_convert_case(trim($nombre), MB_CASE_TITLE, 'UTF-8')) . "</td>
                <td class='text-end small'>$" . number_format($r['monto_efectivo'], 0, ',', '.') . "</td>
                <td class='text-end small'>$" . number_format($r['monto_transferencia'], 0, ',', '.') . "</td>
                <td class='text-end fw-bold text-danger'>$" . number_format($r['monto'], 0, ',', '.') . "</td>
            </tr>";
        }
    }

    $html .= '</tbody></table>';
    return $html;
}

ob_start();

if ($tipo === 'pagos') {
    $stmt = $pdo->prepare("SELECT p.*, per.nombres_completos AS persona
        FROM pagos p
        LEFT JOIN personas per ON p.persona_id = per.id
        WHERE DATE(p.fecha_pago) BETWEEN ? AND ? AND p.estado = 'Completado' $where_pag
        ORDER BY p.fecha_pago DESC");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    echo tablaRegistros($stmt->fetchAll(PDO::FETCH_ASSOC), 'pagos');

} elseif ($tipo === 'otros_ingresos') {
    $stmt = $pdo->prepare("SELECT i.*, p.nombres_completos AS cliente
        FROM ingresos i
        LEFT JOIN personas p ON i.persona_id = p.id
        WHERE DATE(i.fecha_ingreso) BETWEEN ? AND ? AND i.estado = 'Activo' $where_ing
        ORDER BY i.fecha_ingreso DESC");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    echo tablaRegistros($stmt->fetchAll(PDO::FETCH_ASSOC), 'otros_ingresos');

} elseif ($tipo === 'egresos') {
    $stmt = $pdo->prepare("SELECT e.*, p.nombres_completos AS proveedor
        FROM egresos e
        LEFT JOIN personas p ON e.persona_id = p.id
        WHERE DATE(e.fecha_egreso) BETWEEN ? AND ? AND e.estado = 'Activo' $where_egr
        ORDER BY e.fecha_egreso DESC");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    echo tablaRegistros($stmt->fetchAll(PDO::FETCH_ASSOC), 'egresos');

} elseif ($tipo === 'ingresos') {
    // Pagos + Otros ingresos combinados en dos secciones
    $stmt1 = $pdo->prepare("SELECT p.*, per.nombres_completos AS persona
        FROM pagos p LEFT JOIN personas per ON p.persona_id = per.id
        WHERE DATE(p.fecha_pago) BETWEEN ? AND ? AND p.estado = 'Completado' $where_pag
        ORDER BY p.fecha_pago DESC");
    $stmt1->execute([$fecha_inicio, $fecha_fin]);
    $pagos = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT i.*, p.nombres_completos AS cliente
        FROM ingresos i LEFT JOIN personas p ON i.persona_id = p.id
        WHERE DATE(i.fecha_ingreso) BETWEEN ? AND ? AND i.estado = 'Activo' $where_ing
        ORDER BY i.fecha_ingreso DESC");
    $stmt2->execute([$fecha_inicio, $fecha_fin]);
    $otros = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo '<p class="fw-bold small px-3 pt-3 mb-1 text-primary">Pagos Académicos</p>';
    echo tablaRegistros($pagos, 'pagos');
    echo '<p class="fw-bold small px-3 pt-3 mb-1 text-success">Otros Ingresos</p>';
    echo tablaRegistros($otros, 'otros_ingresos');

} elseif ($tipo === 'caja') {
    // Las tres secciones juntas
    $stmt1 = $pdo->prepare("SELECT p.*, per.nombres_completos AS persona
        FROM pagos p LEFT JOIN personas per ON p.persona_id = per.id
        WHERE DATE(p.fecha_pago) BETWEEN ? AND ? AND p.estado = 'Completado' $where_pag
        ORDER BY p.fecha_pago DESC");
    $stmt1->execute([$fecha_inicio, $fecha_fin]);

    $stmt2 = $pdo->prepare("SELECT i.*, p.nombres_completos AS cliente
        FROM ingresos i LEFT JOIN personas p ON i.persona_id = p.id
        WHERE DATE(i.fecha_ingreso) BETWEEN ? AND ? AND i.estado = 'Activo' $where_ing
        ORDER BY i.fecha_ingreso DESC");
    $stmt2->execute([$fecha_inicio, $fecha_fin]);

    $stmt3 = $pdo->prepare("SELECT e.*, p.nombres_completos AS proveedor
        FROM egresos e LEFT JOIN personas p ON e.persona_id = p.id
        WHERE DATE(e.fecha_egreso) BETWEEN ? AND ? AND e.estado = 'Activo' $where_egr
        ORDER BY e.fecha_egreso DESC");
    $stmt3->execute([$fecha_inicio, $fecha_fin]);

    echo '<p class="fw-bold small px-3 pt-3 mb-1 text-primary">Pagos Académicos</p>';
    echo tablaRegistros($stmt1->fetchAll(PDO::FETCH_ASSOC), 'pagos');
    echo '<p class="fw-bold small px-3 pt-3 mb-1 text-success">Otros Ingresos</p>';
    echo tablaRegistros($stmt2->fetchAll(PDO::FETCH_ASSOC), 'otros_ingresos');
    echo '<p class="fw-bold small px-3 pt-3 mb-1 text-danger">Egresos</p>';
    echo tablaRegistros($stmt3->fetchAll(PDO::FETCH_ASSOC), 'egresos');
}

echo ob_get_clean();