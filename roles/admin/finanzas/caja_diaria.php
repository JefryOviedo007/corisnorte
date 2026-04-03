<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('America/Bogota');
require_once __DIR__ . "/../../../config.php";

$pdo->exec("SET time_zone = '-05:00'");

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
$rol          = $_SESSION['rol']      ?? '';
$sede_id      = $_SESSION['sede_id']  ?? null;

// Sedes para filtro Admin
$sedes = [];
if ($rol === 'Admin') {
    $sedes = $pdo->query("SELECT id, nombre FROM sedes WHERE estado = 'Activa' ORDER BY nombre ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);
}

$sede_filtro = ($rol === 'Admin')
    ? (!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null)
    : (int)$sede_id;

// Preservar params GET sin sede_id
$params_base = $_GET;
unset($params_base['sede_id']);
$url_limpiar = '?' . http_build_query($params_base);

// ── Condición de sede para cada tabla ────────────────────────────────────────
// pagos no tiene usuario_id, pero sí persona_id e inscripcion_id
// ingresos y egresos se filtran por usuario_id → usuarios.sede_id

if ($sede_filtro) {
    // Subconsulta de usuario_ids de esa sede
    $stmt_users = $pdo->prepare("SELECT id FROM usuarios WHERE sede_id = ?");
    $stmt_users->execute([$sede_filtro]);
    $usuario_ids = $stmt_users->fetchAll(PDO::FETCH_COLUMN);
    $in_usuarios = !empty($usuario_ids) ? implode(',', $usuario_ids) : '0';

    // Para pagos filtramos por inscripcion → grupo → sede
    $stmt_inscrip = $pdo->prepare(
        "SELECT i.id FROM inscripciones i
         INNER JOIN grupos g ON i.grupo_id = g.id
         WHERE g.sede_id = ?"
    );
    $stmt_inscrip->execute([$sede_filtro]);
    $inscripcion_ids = $stmt_inscrip->fetchAll(PDO::FETCH_COLUMN);
    $in_inscrip = !empty($inscripcion_ids) ? implode(',', $inscripcion_ids) : '0';

    $where_ing = "AND i.usuario_id IN ($in_usuarios)";
    $where_egr = "AND e.usuario_id IN ($in_usuarios)";
    $where_pag = "AND p.inscripcion_id IN ($in_inscrip)";
} else if ($rol !== 'Admin') {
    // Coordinador/Secretaria sin filtro explícito → su sede por sesión
    $stmt_users = $pdo->prepare("SELECT id FROM usuarios WHERE sede_id = ?");
    $stmt_users->execute([$sede_id]);
    $usuario_ids = $stmt_users->fetchAll(PDO::FETCH_COLUMN);
    $in_usuarios = !empty($usuario_ids) ? implode(',', $usuario_ids) : '0';

    $stmt_inscrip = $pdo->prepare(
        "SELECT i.id FROM inscripciones i
         INNER JOIN grupos g ON i.grupo_id = g.id
         WHERE g.sede_id = ?"
    );
    $stmt_inscrip->execute([$sede_id]);
    $inscripcion_ids = $stmt_inscrip->fetchAll(PDO::FETCH_COLUMN);
    $in_inscrip = !empty($inscripcion_ids) ? implode(',', $inscripcion_ids) : '0';

    $where_ing = "AND i.usuario_id IN ($in_usuarios)";
    $where_egr = "AND e.usuario_id IN ($in_usuarios)";
    $where_pag = "AND p.inscripcion_id IN ($in_inscrip)";
} else {
    // Admin sin filtro → todo
    $where_ing = "";
    $where_egr = "";
    $where_pag = "";
}

// ── CONSULTAS ─────────────────────────────────────────────────────────────────

// A. Pagos académicos
$stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(p.monto_efectivo), 0)      AS efec,
        COALESCE(SUM(p.monto_transferencia), 0) AS trans,
        COALESCE(SUM(p.monto), 0)               AS total
    FROM pagos p
    WHERE DATE(p.fecha_pago) BETWEEN ? AND ?
    AND p.estado = 'Completado'
    $where_pag");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$res_pagos = $stmt->fetch(PDO::FETCH_ASSOC);

// B. Otros ingresos
$stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(i.monto_efectivo), 0)      AS efec,
        COALESCE(SUM(i.monto_transferencia), 0) AS trans,
        COALESCE(SUM(i.monto), 0)               AS total
    FROM ingresos i
    WHERE DATE(i.fecha_ingreso) BETWEEN ? AND ?
    AND i.estado = 'Activo'
    $where_ing");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$res_ing = $stmt->fetch(PDO::FETCH_ASSOC);

// C. Egresos
$stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(e.monto_efectivo), 0)      AS efec,
        COALESCE(SUM(e.monto_transferencia), 0) AS trans,
        COALESCE(SUM(e.monto), 0)               AS total
    FROM egresos e
    WHERE DATE(e.fecha_egreso) BETWEEN ? AND ?
    AND e.estado = 'Activo'
    $where_egr");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$res_egr = $stmt->fetch(PDO::FETCH_ASSOC);

// ── TOTALES ───────────────────────────────────────────────────────────────────
$total_ingresos_bruto = ($res_pagos['total'] ?? 0) + ($res_ing['total'] ?? 0);
$total_egresos        = $res_egr['total'] ?? 0;
$saldo_neto           = $total_ingresos_bruto - $total_egresos;
$total_efectivo_caja  = ($res_pagos['efec'] ?? 0) + ($res_ing['efec'] ?? 0) - ($res_egr['efec'] ?? 0);
$total_bancos         = ($res_pagos['trans'] ?? 0) + ($res_ing['trans'] ?? 0) - ($res_egr['trans'] ?? 0);

// Nombre de sede activa para mostrar en el título
$sede_nombre_activa = null;
if ($sede_filtro) {
    $s = $pdo->prepare("SELECT nombre FROM sedes WHERE id = ?");
    $s->execute([$sede_filtro]);
    $sede_nombre_activa = $s->fetchColumn();
} else if ($rol !== 'Admin') {
    $s = $pdo->prepare("SELECT nombre FROM sedes WHERE id = ?");
    $s->execute([$sede_id]);
    $sede_nombre_activa = $s->fetchColumn();
}
?>

<div class="container-fluid py-4">

    <div class="row mb-4 align-items-start">
        <div class="col-md-5">
            <h3 class="fw-bold text-dark">
                <i class="bi bi-cash-stack me-2 text-primary"></i>Caja Diaria / Arqueo
            </h3>
            <p class="text-muted small mb-1">Resumen financiero del periodo seleccionado.</p>
            <?php if ($sede_nombre_activa): ?>
                <span class="badge bg-primary">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($sede_nombre_activa) ?>
                </span>
            <?php else: ?>
                <span class="badge bg-secondary">
                    <i class="bi bi-buildings me-1"></i>Todas las sedes
                </span>
            <?php endif; ?>
        </div>

        <div class="col-md-7">
            <form method="GET" action="" id="formFiltros" class="p-3">
                <?php foreach ($params_base as $key => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endforeach; ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="small fw-bold">Desde:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               class="form-control form-control-sm" value="<?= $fecha_inicio ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Hasta:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                               class="form-control form-control-sm" value="<?= $fecha_fin ?>">
                    </div>
                    <?php if ($rol === 'Admin'): ?>
                    <div class="col-md-4">
                        <label class="small fw-bold">Sede:</label>
                        <select name="sede_id" id="select_sede" class="form-select form-select-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>" <?= $sede_filtro == $sede['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sede['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size:.7rem;">Total Ingresos</small>
                        <h4 class="fw-bold mb-2 text-success">$<?= number_format($total_ingresos_bruto, 0, ',', '.') ?></h4>
                    </div>
                    <button class="btn btn-sm btn-light border" onclick="abrirModal('ingresos')" title="Ver detalle">
                        <i class="bi bi-eye text-success"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1" style="font-size:.72rem;">
                        <i class="bi bi-cash me-1"></i>Efectivo: $<?= number_format(($res_pagos['efec'] ?? 0) + ($res_ing['efec'] ?? 0), 0, ',', '.') ?>
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info px-2 py-1" style="font-size:.72rem;">
                        <i class="bi bi-bank me-1"></i>Transfer: $<?= number_format(($res_pagos['trans'] ?? 0) + ($res_ing['trans'] ?? 0), 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-danger border-4 p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size:.7rem;">Total Egresos</small>
                        <h4 class="fw-bold mb-2 text-danger">$<?= number_format($total_egresos, 0, ',', '.') ?></h4>
                    </div>
                    <button class="btn btn-sm btn-light border" onclick="abrirModal('egresos')" title="Ver detalle">
                        <i class="bi bi-eye text-danger"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1" style="font-size:.72rem;">
                        <i class="bi bi-cash me-1"></i>Efectivo: $<?= number_format($res_egr['efec'] ?? 0, 0, ',', '.') ?>
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1" style="font-size:.72rem;">
                        <i class="bi bi-bank me-1"></i>Transfer: $<?= number_format($res_egr['trans'] ?? 0, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 p-3 <?= $saldo_neto >= 0 ? 'border-primary' : 'border-danger' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size:.7rem;">Total en Caja</small>
                        <h4 class="fw-bold mb-2 <?= $saldo_neto >= 0 ? 'text-primary' : 'text-danger' ?>">
                            <?= $saldo_neto < 0 ? '-' : '' ?>$<?= number_format(abs($saldo_neto), 0, ',', '.') ?>
                        </h4>
                    </div>
                    <button class="btn btn-sm btn-light border" onclick="abrirModal('caja')" title="Ver detalle">
                        <i class="bi bi-eye text-primary"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1" style="font-size:.72rem;">
                        <i class="bi bi-cash me-1"></i>Efectivo: $<?= number_format($total_efectivo_caja, 0, ',', '.') ?>
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info px-2 py-1" style="font-size:.72rem;">
                        <i class="bi bi-bank me-1"></i>Transfer: $<?= number_format($total_bancos, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-3">Distribución por Método de Pago</h6>
                <canvas id="chartFinanzas" style="max-height:300px;"></canvas>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Balance del Periodo</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">

                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small">Pagos Académicos</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold">$<?= number_format($res_pagos['total'] ?? 0, 0, ',', '.') ?></span>
                                    <button class="btn btn-sm btn-light border p-1 lh-1" onclick="abrirModal('pagos')" title="Ver pagos">
                                        <i class="bi bi-eye small text-primary"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                <span class="badge bg-success-subtle text-success" style="font-size:.68rem;">
                                    <i class="bi bi-cash me-1"></i>$<?= number_format($res_pagos['efec'] ?? 0, 0, ',', '.') ?>
                                </span>
                                <span class="badge bg-info-subtle text-info" style="font-size:.68rem;">
                                    <i class="bi bi-bank me-1"></i>$<?= number_format($res_pagos['trans'] ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                        </li>

                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small">Otros Ingresos</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold">$<?= number_format($res_ing['total'] ?? 0, 0, ',', '.') ?></span>
                                    <button class="btn btn-sm btn-light border p-1 lh-1" onclick="abrirModal('otros_ingresos')" title="Ver ingresos">
                                        <i class="bi bi-eye small text-success"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                <span class="badge bg-success-subtle text-success" style="font-size:.68rem;">
                                    <i class="bi bi-cash me-1"></i>$<?= number_format($res_ing['efec'] ?? 0, 0, ',', '.') ?>
                                </span>
                                <span class="badge bg-info-subtle text-info" style="font-size:.68rem;">
                                    <i class="bi bi-bank me-1"></i>$<?= number_format($res_ing['trans'] ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                        </li>

                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small text-danger">Egresos Totales</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-danger">-$<?= number_format($total_egresos, 0, ',', '.') ?></span>
                                    <button class="btn btn-sm btn-light border p-1 lh-1" onclick="abrirModal('egresos')" title="Ver egresos">
                                        <i class="bi bi-eye small text-danger"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                <span class="badge bg-danger-subtle text-danger" style="font-size:.68rem;">
                                    <i class="bi bi-cash me-1"></i>$<?= number_format($res_egr['efec'] ?? 0, 0, ',', '.') ?>
                                </span>
                                <span class="badge bg-warning-subtle text-warning" style="font-size:.68rem;">
                                    <i class="bi bi-bank me-1"></i>$<?= number_format($res_egr['trans'] ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                        </li>

                        <li class="list-group-item px-3 py-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold <?= $saldo_neto >= 0 ? 'text-primary' : 'text-danger' ?>">Total en Caja</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold h6 mb-0 <?= $saldo_neto >= 0 ? 'text-primary' : 'text-danger' ?>">
                                        <?= $saldo_neto < 0 ? '-' : '' ?>$<?= number_format(abs($saldo_neto), 0, ',', '.') ?>
                                    </span>
                                    <button class="btn btn-sm btn-light border p-1 lh-1" onclick="abrirModal('caja')" title="Ver detalle">
                                        <i class="bi bi-eye small text-primary"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                <span class="badge bg-primary-subtle text-primary" style="font-size:.68rem;">
                                    <i class="bi bi-cash me-1"></i>$<?= number_format($total_efectivo_caja, 0, ',', '.') ?>
                                </span>
                                <span class="badge bg-info-subtle text-info" style="font-size:.68rem;">
                                    <i class="bi bi-bank me-1"></i>$<?= number_format($total_bancos, 0, ',', '.') ?>
                                </span>
                            </div>
                        </li>

                    </ul>
                    <div class="alert alert-warning mx-3 my-2 py-2 small mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Saldo neto = ingresos totales menos egresos del periodo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header" id="modalDetalleHeader">
                <h5 class="modal-title fw-bold" id="modalDetalleTitulo">Detalle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="modalDetalleContenido" class="p-3">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Cargando...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Gráfica ───────────────────────────────────────────────────────────────────
const ctx = document.getElementById('chartFinanzas').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Efectivo', 'Transferencias'],
        datasets: [{
            label: 'Ingresos',
            data: [
                <?= ($res_pagos['efec'] ?? 0) + ($res_ing['efec'] ?? 0) ?>,
                <?= ($res_pagos['trans'] ?? 0) + ($res_ing['trans'] ?? 0) ?>
            ],
            backgroundColor: '#198754'
        }, {
            label: 'Egresos',
            data: [
                <?= $res_egr['efec'] ?? 0 ?>,
                <?= $res_egr['trans'] ?? 0 ?>
            ],
            backgroundColor: '#dc3545'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: {
            callback: v => '$' + v.toLocaleString('es-CO')
        }}}
    }
});

// ── Auto-submit ───────────────────────────────────────────────────────────────
document.getElementById('fecha_inicio').addEventListener('change', () => document.getElementById('formFiltros').submit());
document.getElementById('fecha_fin').addEventListener('change',    () => document.getElementById('formFiltros').submit());
<?php if ($rol === 'Admin'): ?>
document.getElementById('select_sede').addEventListener('change',  () => document.getElementById('formFiltros').submit());
<?php endif; ?>

// ── Modal con detalle por tipo ────────────────────────────────────────────────
const configModal = {
    pagos: {
        titulo: 'Pagos Académicos',
        color:  'bg-primary text-white',
        url:    'roles/admin/finanzas/caja/detalle.php?tipo=pagos'
    },
    otros_ingresos: {
        titulo: 'Otros Ingresos',
        color:  'bg-success text-white',
        url:    'roles/admin/finanzas/caja/detalle.php?tipo=otros_ingresos'
    },
    egresos: {
        titulo: 'Egresos',
        color:  'bg-danger text-white',
        url:    'roles/admin/finanzas/caja/detalle.php?tipo=egresos'
    },
    ingresos: {
        titulo: 'Total Ingresos (Pagos + Otros)',
        color:  'bg-success text-white',
        url:    'roles/admin/finanzas/caja/detalle.php?tipo=ingresos'
    },
    caja: {
        titulo: 'Resumen Total en Caja',
        color:  'bg-primary text-white',
        url:    'roles/admin/finanzas/caja/detalle.php?tipo=caja'
    }
};

function abrirModal(tipo) {
    const cfg = configModal[tipo];
    const modalEl = document.getElementById('modalDetalle');

    // Título y color header
    document.getElementById('modalDetalleTitulo').textContent = cfg.titulo;
    document.getElementById('modalDetalleHeader').className   = 'modal-header ' + cfg.color;

    // Spinner mientras carga
    document.getElementById('modalDetalleContenido').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted small">Cargando registros...</p>
        </div>`;

    // Armar URL con los filtros actuales de fecha y sede
    const params = new URLSearchParams({
        tipo:          tipo,
        fecha_inicio:  document.getElementById('fecha_inicio').value,
        fecha_fin:     document.getElementById('fecha_fin').value,
        <?php if ($sede_filtro): ?>
        sede_id: '<?= $sede_filtro ?>',
        <?php elseif ($rol !== 'Admin'): ?>
        sede_id: '<?= $sede_id ?>',
        <?php endif; ?>
    });

    fetch('roles/admin/finanzas/caja/detalle.php?' + params.toString())
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalDetalleContenido').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('modalDetalleContenido').innerHTML =
                '<div class="alert alert-danger m-3">Error al cargar los datos.</div>';
        });

    new bootstrap.Modal(modalEl).show();
}
</script>