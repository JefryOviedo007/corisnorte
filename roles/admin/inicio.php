<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "config.php";

$sede_id_session = $_SESSION['sede_id'] ?? null;
$rol             = $_SESSION['rol']     ?? '';
$nombre_usuario  = explode(' ', $_SESSION['nombre'] ?? 'Usuario')[0];

// Sedes para filtro Admin
$sedes = [];
if ($rol === 'Admin') {
    $sedes = $pdo->query("SELECT id, nombre FROM sedes WHERE estado = 'Activa' ORDER BY nombre ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);
}

// Sede activa según rol
$sede_filtro = ($rol === 'Admin')
    ? (!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null)
    : (int)$sede_id_session;

// Preservar params GET
$params_base = $_GET;
unset($params_base['sede_id']);

// Nombre de sede para mostrar en encabezado
$sede_nombre_activa = null;
if ($sede_filtro) {
    $s = $pdo->prepare("SELECT nombre FROM sedes WHERE id = ?");
    $s->execute([$sede_filtro]);
    $sede_nombre_activa = $s->fetchColumn();
} else if ($rol !== 'Admin') {
    $s = $pdo->prepare("SELECT nombre FROM sedes WHERE id = ?");
    $s->execute([$sede_id_session]);
    $sede_nombre_activa = $s->fetchColumn();
}

// ── Condiciones WHERE según sede ─────────────────────────────────────────────
$sede_activa = $sede_filtro ?? ($rol !== 'Admin' ? $sede_id_session : null);

if ($sede_activa) {
    // IDs de usuarios de esa sede
    $stmt_u = $pdo->prepare("SELECT id FROM usuarios WHERE sede_id = ?");
    $stmt_u->execute([$sede_activa]);
    $usuario_ids = $stmt_u->fetchAll(PDO::FETCH_COLUMN);
    $in_usuarios = !empty($usuario_ids) ? implode(',', $usuario_ids) : '0';

    // IDs de inscripciones de esa sede
    $stmt_i = $pdo->prepare(
        "SELECT i.id FROM inscripciones i
         INNER JOIN grupos g ON i.grupo_id = g.id
         WHERE g.sede_id = ?"
    );
    $stmt_i->execute([$sede_activa]);
    $inscripcion_ids = $stmt_i->fetchAll(PDO::FETCH_COLUMN);
    $in_inscrip = !empty($inscripcion_ids) ? implode(',', $inscripcion_ids) : '0';

    $where_sede     = "AND sede_id = $sede_activa";
    $where_usuario  = "AND usuario_id IN ($in_usuarios)";
    $where_pago     = "AND inscripcion_id IN ($in_inscrip)";
    $where_grupo    = "AND sede_id = $sede_activa";
} else {
    // Admin sin filtro → todo
    $where_sede    = "";
    $where_usuario = "";
    $where_pago    = "";
    $where_grupo   = "";
}

try {
    $mes_actual = date('Y-m');

    // KPIs académicos
    $estudiantes_activos = $pdo->query(
        "SELECT COUNT(*) FROM personas WHERE estado = 'En formación' $where_sede"
    )->fetchColumn();

    $total_matriculados = $pdo->query(
        "SELECT COUNT(*) FROM personas WHERE estado = 'Matriculado' $where_sede"
    )->fetchColumn();

    $prospectos_mes = $pdo->query(
        "SELECT COUNT(*) FROM personas 
         WHERE estado = 'Prospecto' 
         AND MONTH(fecha_creacion) = MONTH(CURRENT_DATE()) 
         AND YEAR(fecha_creacion)  = YEAR(CURRENT_DATE())
         $where_sede"
    )->fetchColumn();

    // Finanzas mes actual
    $ing_pagos = $pdo->query(
        "SELECT COALESCE(SUM(monto), 0) FROM pagos 
         WHERE estado = 'Completado' 
         AND DATE_FORMAT(fecha_pago, '%Y-%m') = '$mes_actual'
         $where_pago"
    )->fetchColumn();

    $ing_otros = $pdo->query(
        "SELECT COALESCE(SUM(monto), 0) FROM ingresos 
         WHERE estado = 'Activo' 
         AND DATE_FORMAT(fecha_ingreso, '%Y-%m') = '$mes_actual'
         $where_usuario"
    )->fetchColumn();

    $total_ingresos_mes = $ing_pagos + $ing_otros;

    $total_egresos_mes = $pdo->query(
        "SELECT COALESCE(SUM(monto), 0) FROM egresos 
         WHERE estado = 'Activo' 
         AND DATE_FORMAT(fecha_egreso, '%Y-%m') = '$mes_actual'
         $where_usuario"
    )->fetchColumn();

    // Grupos críticos
    $grupos_criticos = $pdo->query(
        "SELECT COUNT(*) FROM grupos 
         WHERE cupos_disponibles <= 3 
         AND estado = 'Inscripciones Abiertas'
         $where_grupo"
    )->fetchColumn();

    // Tendencia 7 días
    $dias = []; $data_ingresos = []; $data_egresos = [];
    for ($i = 6; $i >= 0; $i--) {
        $fecha  = date('Y-m-d', strtotime("-$i days"));
        $dias[] = date('d M', strtotime($fecha));

        $ing_d = $pdo->query(
            "SELECT COALESCE(SUM(monto), 0) FROM pagos 
             WHERE DATE(fecha_pago) = '$fecha' AND estado = 'Completado' $where_pago"
        )->fetchColumn();

        $ing_o = $pdo->query(
            "SELECT COALESCE(SUM(monto), 0) FROM ingresos 
             WHERE DATE(fecha_ingreso) = '$fecha' AND estado = 'Activo' $where_usuario"
        )->fetchColumn();

        $data_ingresos[] = $ing_d + $ing_o;

        $data_egresos[] = $pdo->query(
            "SELECT COALESCE(SUM(monto), 0) FROM egresos 
             WHERE DATE(fecha_egreso) = '$fecha' AND estado = 'Activo' $where_usuario"
        )->fetchColumn();
    }

    // Doughnut distribución personas
    $distribucion = $pdo->query(
        "SELECT estado, COUNT(*) as cant FROM personas WHERE 1=1 $where_sede GROUP BY estado"
    )->fetchAll(PDO::FETCH_ASSOC);

    $labels_pie = []; $data_pie = [];
    foreach ($distribucion as $d) {
        $labels_pie[] = $d['estado'];
        $data_pie[]   = $d['cant'];
    }

    // Grupos en formación
    $stmt_grupos = $pdo->prepare(
        "SELECT g.nombre, g.jornada, g.cupos, g.cupos_disponibles
         FROM grupos g
         WHERE g.estado = 'En Formación'
         $where_grupo
         LIMIT 5"
    );
    $stmt_grupos->execute();
    $grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error en el sistema: " . $e->getMessage());
}
?>

<style>
    :root { --primary-dark: #1e293b; --accent: #3b82f6; }
    .card-custom { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: white; transition: all 0.3s ease; }
    .card-custom:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.07); }
    .icon-box { width: 48px; height: 48px; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .bg-soft-blue   { background: #eff6ff; color: #2563eb; }
    .bg-soft-green  { background: #f0fdf4; color: #16a34a; }
    .bg-soft-red    { background: #fef2f2; color: #dc2626; }
    .bg-soft-orange { background: #fff7ed; color: #ea580c; }
    .table-elite thead { background: #f8fafc; }
    .table-elite th { font-size: 0.7rem; text-transform: uppercase; color: #64748b; padding: 1rem; }
    .progress { background-color: #f1f5f9; border-radius: 10px; }
</style>

<div class="container-fluid py-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0">Panel de Control</h2>
            <p class="text-muted small mb-1">
                Bienvenido, <strong><?= htmlspecialchars($nombre_usuario) ?></strong>.
                Rendimiento académico y financiero.
            </p>
            <?php if ($sede_nombre_activa): ?>
                <span class="badge bg-primary">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($sede_nombre_activa) ?>
                </span>
            <?php elseif ($rol === 'Admin'): ?>
                <span class="badge bg-secondary">
                    <i class="bi bi-buildings me-1"></i>Todas las sedes
                </span>
            <?php endif; ?>
        </div>

        <?php if ($rol === 'Admin'): ?>
        <div class="col-md-6 d-flex justify-content-md-end mt-3 mt-md-0">
            <form method="GET" action="" id="formSede" class="d-flex align-items-center gap-2">
                <?php foreach ($params_base as $key => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endforeach; ?>
                <label class="fw-bold small mb-0 text-nowrap">
                    <i class="bi bi-building me-1"></i> Sede:
                </label>
                <select name="sede_id" id="select_sede_dash" class="form-select form-select-sm" style="max-width:220px;">
                    <option value="">— Todas las sedes —</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= $sede['id'] ?>" <?= $sede_filtro == $sede['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sede['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($sede_filtro): ?>
                    <a href="?<?= http_build_query($params_base) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1">EN FORMACIÓN</p>
                        <h3 class="fw-bold mb-0"><?= number_format($estudiantes_activos) ?></h3>
                    </div>
                    <div class="icon-box bg-soft-blue"><i class="bi bi-mortarboard-fill"></i></div>
                </div>
                <div class="mt-3 small text-primary"><i class="bi bi-person-check me-1"></i> Estudiantes activos</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1">PROSPECTOS MES</p>
                        <h3 class="fw-bold mb-0"><?= number_format($prospectos_mes) ?></h3>
                    </div>
                    <div class="icon-box bg-soft-orange"><i class="bi bi-funnel-fill"></i></div>
                </div>
                <div class="mt-3 small text-muted">Nuevos leads del mes</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1">INGRESOS <?= strtoupper(date('M')) ?></p>
                        <h3 class="fw-bold mb-0 text-success">$<?= number_format($total_ingresos_mes, 0, ',', '.') ?></h3>
                    </div>
                    <div class="icon-box bg-soft-green"><i class="bi bi-currency-dollar"></i></div>
                </div>
                <div class="mt-3 small text-danger">
                    Egresos: $<?= number_format($total_egresos_mes, 0, ',', '.') ?>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4 <?= $grupos_criticos > 0 ? 'border-start border-danger border-4' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1">CUPOS CRÍTICOS</p>
                        <h3 class="fw-bold mb-0"><?= $grupos_criticos ?></h3>
                    </div>
                    <div class="icon-box bg-soft-red"><i class="bi bi-exclamation-triangle-fill"></i></div>
                </div>
                <div class="mt-3 small text-danger fw-bold">
                    <?= $grupos_criticos > 0 ? 'Requieren atención inmediata' : 'Sin alertas activas' ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0">Rendimiento Financiero Semanal</h6>
                    <span class="small text-muted">Últimos 7 días</span>
                </div>
                <div style="height:300px;">
                    <canvas id="chartFlujo"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-custom p-4 h-100 text-center">
                <h6 class="fw-bold mb-3">Segmentación de Población</h6>
                <div style="height:220px;">
                    <canvas id="pieChart"></canvas>
                </div>
                <hr>
                <div class="row g-2 mt-1">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block small">Matriculados</small>
                        <span class="fw-bold"><?= $total_matriculados ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block small">En Formación</small>
                        <span class="fw-bold text-primary"><?= $estudiantes_activos ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-custom overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Gestión de Grupos (En Formación)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-elite align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Programa / Grupo</th>
                                <th>Jornada</th>
                                <th>Estudiantes</th>
                                <th>Ocupación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grupos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle me-2"></i>No hay grupos en formación.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($grupos as $g):
                                    $inscritos   = $g['cupos'] - $g['cupos_disponibles'];
                                    $porcentaje  = ($g['cupos'] > 0) ? round(($inscritos / $g['cupos']) * 100) : 0;
                                    $color_barra = $porcentaje >= 90 ? 'bg-danger' : ($porcentaje >= 70 ? 'bg-warning' : 'bg-success');
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($g['nombre']) ?></td>
                                    <td><span class="badge bg-light text-dark border px-3 py-2"><?= $g['jornada'] ?></span></td>
                                    <td><i class="bi bi-people me-1"></i><?= $inscritos ?> / <?= $g['cupos'] ?></td>
                                    <td style="width:200px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar <?= $color_barra ?>" style="width:<?= $porcentaje ?>%"></div>
                                            </div>
                                            <span class="small fw-bold"><?= $porcentaje ?>%</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success-subtle text-success border border-success px-3">En Curso</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.color = '#64748b';

// Gráfica línea
new Chart(document.getElementById('chartFlujo').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dias) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode($data_ingresos) ?>,
            borderColor: '#16a34a',
            backgroundColor: 'rgba(22,163,74,0.06)',
            fill: true, tension: 0.4, borderWidth: 3,
            pointBackgroundColor: '#16a34a'
        }, {
            label: 'Egresos',
            data: <?= json_encode($data_egresos) ?>,
            borderColor: '#dc2626',
            backgroundColor: 'transparent',
            borderDash: [5,5], tension: 0.4, borderWidth: 2
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', align: 'end' } },
        scales: {
            y: { grid: { borderDash: [2,2] }, ticks: { callback: v => '$' + v.toLocaleString('es-CO') } },
            x: { grid: { display: false } }
        }
    }
});

// Doughnut
new Chart(document.getElementById('pieChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($labels_pie) ?>,
        datasets: [{
            data: <?= json_encode($data_pie) ?>,
            backgroundColor: ['#1e293b','#3b82f6','#10b981','#f59e0b','#ef4444'],
            hoverOffset: 15, borderWidth: 4, borderColor: '#fff'
        }]
    },
    options: {
        cutout: '80%', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, usePointStyle: true } } }
    }
});

// Auto-submit al cambiar sede
<?php if ($rol === 'Admin'): ?>
document.getElementById('select_sede_dash').addEventListener('change', function() {
    document.getElementById('formSede').submit();
});
<?php endif; ?>
</script>