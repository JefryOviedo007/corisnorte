<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . "/../../../config.php";

// 1. Parámetros de fecha (Por defecto: Hoy)
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin    = $_GET['fecha_fin'] ?? date('Y-m-d');
$sede_id      = $_SESSION['sede_id'] ?? null;
$rol          = $_SESSION['rol'] ?? '';

// Filtro de sede para Admin vs Coordinador
$where_sede = ($rol !== 'Admin') ? " AND sede_id = '$sede_id'" : "";
// Nota: Para ingresos/egresos/pagos, asumimos que están ligados a usuarios/sedes. 
// Si las tablas ingresos/egresos no tienen sede_id, se filtran por el usuario_id del staff de esa sede.

/* |--------------------------------------------------------------------------
| CONSULTAS CONSOLIDADAS
|--------------------------------------------------------------------------
*/

// A. TOTAL PAGOS (Inscripciones, Pensiones, etc)
$sql_pagos = "SELECT SUM(monto_efectivo) as efec, SUM(monto_transferencia) as trans, SUM(monto) as total 
              FROM pagos WHERE DATE(fecha_pago) BETWEEN ? AND ? AND estado = 'Completado'";
$stmt = $pdo->prepare($sql_pagos);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$res_pagos = $stmt->fetch(PDO::FETCH_ASSOC);

// B. OTROS INGRESOS
$sql_ing = "SELECT SUM(monto_efectivo) as efec, SUM(monto_transferencia) as trans, SUM(monto) as total 
            FROM ingresos WHERE DATE(fecha_ingreso) BETWEEN ? AND ? AND estado = 'Activo'";
$stmt = $pdo->prepare($sql_ing);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$res_ing = $stmt->fetch(PDO::FETCH_ASSOC);

// C. EGRESOS (Gastos)
$sql_egr = "SELECT SUM(monto_efectivo) as efec, SUM(monto_transferencia) as trans, SUM(monto) as total 
            FROM egresos WHERE DATE(fecha_egresotimestamp) BETWEEN ? AND ? AND estado = 'Activo'";
// Nota: Ajusta 'fecha_egresotimestamp' si el nombre real es 'fecha_egreso' en tu DB.
$stmt = $pdo->prepare("SELECT SUM(monto_efectivo) as efec, SUM(monto_transferencia) as trans, SUM(monto) as total FROM egresos WHERE DATE(fecha_egreso) BETWEEN ? AND ? AND estado = 'Activo'");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$res_egr = $stmt->fetch(PDO::FETCH_ASSOC);

// TOTALES GENERALES
$total_ingresos_bruto = ($res_pagos['total'] ?? 0) + ($res_ing['total'] ?? 0);
$total_egresos        = $res_egr['total'] ?? 0;
$saldo_neto           = $total_ingresos_bruto - $total_egresos;

$total_efectivo_caja  = ($res_pagos['efec'] ?? 0) + ($res_ing['efec'] ?? 0) - ($res_egr['efec'] ?? 0);
$total_bancos         = ($res_pagos['trans'] ?? 0) + ($res_ing['trans'] ?? 0) - ($res_egr['trans'] ?? 0);
?>

<div class="container-fluid py-4" style="background: #f8f9fa;">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark"><i class="bi bi-cash-stack me-2 text-primary"></i>Caja Diaria / Arqueo</h3>
            <p class="text-muted small">Resumen financiero detallado del periodo.</p>
        </div>
        <div class="col-md-6">
            <form class="card border-0 shadow-sm p-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="small fw-bold">Desde:</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?= $fecha_inicio ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="small fw-bold">Hasta:</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?= $fecha_fin ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 p-3">
                <small class="text-muted fw-bold">TOTAL INGRESOS</small>
                <h4 class="fw-bold mb-0 text-success">$<?= number_format($total_ingresos_bruto, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4 p-3">
                <small class="text-muted fw-bold">TOTAL EGRESOS</small>
                <h4 class="fw-bold mb-0 text-danger">$<?= number_format($total_egresos, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 p-3">
                <small class="text-muted fw-bold">DISPONIBLE EN EFECTIVO</small>
                <h4 class="fw-bold mb-0 text-primary">$<?= number_format($total_efectivo_caja, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 p-3">
                <small class="text-muted fw-bold">NETO EN BANCOS</small>
                <h4 class="fw-bold mb-0 text-info">$<?= number_format($total_bancos, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-3">Distribución por Método de Pago</h6>
                <canvas id="chartFinanzas" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0">Balance de Periodo</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Ingresos Académicos (Pagos)</span>
                            <span class="fw-bold text-dark">$<?= number_format($res_pagos['total'] ?? 0, 0, ',', '.') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Otros Ingresos</span>
                            <span class="fw-bold text-dark">$<?= number_format($res_ing['total'] ?? 0, 0, ',', '.') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger">
                            <span>Egresos Totales</span>
                            <span class="fw-bold">-$<?= number_format($total_egresos, 0, ',', '.') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 mt-2">
                            <span class="h5 fw-bold text-primary">SALDO NETO</span>
                            <span class="h5 fw-bold text-primary">$<?= number_format($saldo_neto, 0, ',', '.') ?></span>
                        </li>
                    </ul>
                    <div class="alert alert-warning mt-3 py-2 small">
                        <i class="bi bi-info-circle me-1"></i> El saldo neto considera ingresos menos gastos totales del periodo seleccionado.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartFinanzas').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Efectivo', 'Transferencias'],
        datasets: [{
            label: 'Ingresos',
            data: [
                <?= ($res_pagos['efec'] + $res_ing['efec']) ?>, 
                <?= ($res_pagos['trans'] + $res_ing['trans']) ?>
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
        scales: { y: { beginAtZero: true } }
    }
});
</script>