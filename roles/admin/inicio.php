<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "config.php";

// 1. Identificación de Contexto (Sede y Rol)
$sede_id_session = $_SESSION['sede_id'] ?? null;
$rol = $_SESSION['rol'] ?? '';
$nombre_usuario = explode(' ', $_SESSION['nombre'] ?? 'Usuario')[0];

// Filtros de seguridad por Sede
$where_sede = ($rol !== 'Admin') ? " AND sede_id = '$sede_id_session'" : "";
$where_usuario = ($rol !== 'Admin') ? " AND usuario_id IN (SELECT id FROM usuarios WHERE sede_id = '$sede_id_session')" : "";

try {
    // --- KPIs OPERATIVOS Y ACADÉMICOS ---
    $estudiantes_activos = $pdo->query("SELECT COUNT(*) FROM personas WHERE estado = 'En formación' $where_sede")->fetchColumn();
    $total_matriculados = $pdo->query("SELECT COUNT(*) FROM personas WHERE estado = 'Matriculado' $where_sede")->fetchColumn();
    $prospectos_mes = $pdo->query("SELECT COUNT(*) FROM personas WHERE estado = 'Prospecto' AND MONTH(fecha_creacion) = MONTH(CURRENT_DATE()) $where_sede")->fetchColumn();
    
    // --- FINANZAS (MES ACTUAL) ---
    $mes_actual = date('Y-m');
    $ing_pagos = $pdo->query("SELECT SUM(monto) FROM pagos WHERE estado = 'Completado' AND DATE_FORMAT(fecha_pago, '%Y-%m') = '$mes_actual' $where_usuario")->fetchColumn() ?? 0;
    $ing_otros = $pdo->query("SELECT SUM(monto) FROM ingresos WHERE estado = 'Activo' AND DATE_FORMAT(fecha_ingreso, '%Y-%m') = '$mes_actual' $where_usuario")->fetchColumn() ?? 0;
    $total_ingresos_mes = $ing_pagos + $ing_otros;
    $total_egresos_mes = $pdo->query("SELECT SUM(monto) FROM egresos WHERE estado = 'Activo' AND DATE_FORMAT(fecha_egreso, '%Y-%m') = '$mes_actual' $where_usuario")->fetchColumn() ?? 0;

    // --- ALERTAS DE CAPACIDAD ---
    $grupos_criticos = $pdo->query("SELECT COUNT(*) FROM grupos WHERE cupos_disponibles <= 3 AND estado = 'Inscripciones Abiertas' $where_sede")->fetchColumn();

    // --- DATOS PARA GRÁFICO DE TENDENCIA (7 DÍAS) ---
    $dias = []; $data_ingresos = []; $data_egresos = [];
    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $dias[] = date('d M', strtotime($fecha));
        $ing_d = $pdo->query("SELECT SUM(monto) FROM pagos WHERE DATE(fecha_pago) = '$fecha' AND estado = 'Completado' $where_usuario")->fetchColumn() ?? 0;
        $ing_o = $pdo->query("SELECT SUM(monto) FROM ingresos WHERE DATE(fecha_ingreso) = '$fecha' AND estado = 'Activo' $where_usuario")->fetchColumn() ?? 0;
        $data_ingresos[] = $ing_d + $ing_o;
        $data_egresos[] = $pdo->query("SELECT SUM(monto) FROM egresos WHERE DATE(fecha_egreso) = '$fecha' AND estado = 'Activo' $where_usuario")->fetchColumn() ?? 0;
    }

    // --- DATOS PARA DOUGHNUT (DISTRIBUCIÓN) ---
    $distribucion = $pdo->query("SELECT estado, COUNT(*) as cant FROM personas WHERE 1=1 $where_sede GROUP BY estado")->fetchAll(PDO::FETCH_ASSOC);
    $labels_pie = []; $data_pie = [];
    foreach($distribucion as $d) { $labels_pie[] = $d['estado']; $data_pie[] = $d['cant']; }

} catch (Exception $e) { die("Error en el sistema: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Institucional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-dark: #1e293b; --accent: #3b82f6; }
        body { background: #f1f5f9; font-family: 'Inter', system-ui, sans-serif; color: #334155; }
        
        /* Margen superior corregido para evitar desbordamiento */
        .main-container { padding-top: 5rem; padding-bottom: 3rem; }
        
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: white; transition: all 0.3s ease; }
        .card-custom:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        .icon-box { width: 48px; height: 48px; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .bg-soft-blue { background: #eff6ff; color: #2563eb; }
        .bg-soft-green { background: #f0fdf4; color: #16a34a; }
        .bg-soft-red { background: #fef2f2; color: #dc2626; }
        .bg-soft-orange { background: #fff7ed; color: #ea580c; }

        .filter-header { background: white; border-bottom: 1px solid #e2e8f0; position: fixed; top: 0; width: 100%; z-index: 1000; height: 70px; display: flex; align-items: center; }
        .table-elite thead { background: #f8fafc; border-radius: 0.5rem; }
        .table-elite th { font-size: 0.7rem; text-transform: uppercase; color: #64748b; padding: 1rem; }
        .progress { background-color: #f1f5f9; border-radius: 10px; }
    </style>
</head>
<body>

<div class="container main-container">
    <div class="row mb-4">
        <div class="col-12 text-center text-md-start">
            <h2 class="fw-bold">Análisis Institucional</h2>
            <p class="text-muted small">Bienvenido, <?= $nombre_usuario ?>. Aquí tienes el rendimiento académico y financiero.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1 uppercase">EN FORMACIÓN</p>
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
                        <p class="text-muted small fw-bold mb-1 uppercase">PROSPECTOS MES</p>
                        <h3 class="fw-bold mb-0"><?= number_format($prospectos_mes) ?></h3>
                    </div>
                    <div class="icon-box bg-soft-orange"><i class="bi bi-funnel-fill"></i></div>
                </div>
                <div class="mt-3 small text-muted">Meta: <?= number_format($prospectos_mes + 5) ?> / mes</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1 uppercase">INGRESOS <?= date('M') ?></p>
                        <h3 class="fw-bold mb-0 text-success">$<?= number_format($total_ingresos_mes, 0, ',', '.') ?></h3>
                    </div>
                    <div class="icon-box bg-soft-green"><i class="bi bi-currency-dollar"></i></div>
                </div>
                <div class="mt-3 small text-success"><i class="bi bi-graph-up-arrow me-1"></i> Flujo de caja positivo</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom p-4 <?= ($grupos_criticos > 0) ? 'border-start border-danger border-4' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold mb-1 uppercase">CUPOS CRÍTICOS</p>
                        <h3 class="fw-bold mb-0"><?= $grupos_criticos ?></h3>
                    </div>
                    <div class="icon-box bg-soft-red"><i class="bi bi-exclamation-triangle-fill"></i></div>
                </div>
                <div class="mt-3 small text-danger fw-bold">Requieren atención inmediata</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0">Rendimiento Financiero Semanal</h6>
                    <span class="small text-muted">Últimos 7 días</span>
                </div>
                <div style="height: 350px;">
                    <canvas id="chartFlujo"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom p-4 h-100 text-center">
                <h6 class="fw-bold mb-4">Segmentación de Población</h6>
                <div style="height: 250px;">
                    <canvas id="pieChart"></canvas>
                </div>
                <hr>
                <div class="row g-2 mt-2">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block small">Matriculados</small>
                        <span class="fw-bold"><?= $total_matriculados ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block small">Activos</small>
                        <span class="fw-bold text-primary"><?= $estudiantes_activos ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card card-custom overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Gestión de Grupos (En Formación)</h6>
                    <a href="dashboard.php?page=grupos" class="btn btn-light btn-sm fw-bold border">Ver Todos</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-elite align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre del Programa / Grupo</th>
                                <th>Jornada</th>
                                <th>Estudiantes</th>
                                <th>Ocupación (%)</th>
                                <th>Estado Académico</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT nombre, jornada, cupos, cupos_disponibles FROM grupos WHERE estado = 'En Formación' $where_sede LIMIT 5");
                            $stmt->execute();
                            while($g = $stmt->fetch()): 
                                $inscritos = $g['cupos'] - $g['cupos_disponibles'];
                                $porcentaje = ($g['cupos'] > 0) ? round(($inscritos / $g['cupos']) * 100) : 0;
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= $g['nombre'] ?></div>
                                    <small class="text-muted">ID-0<?= rand(10,99) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark px-3 py-2"><?= $g['jornada'] ?></span></td>
                                <td><i class="bi bi-people me-2"></i><?= $inscritos ?> / <?= $g['cupos'] ?></td>
                                <td style="width: 200px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-accent" style="width: <?= $porcentaje ?>%"></div>
                                        </div>
                                        <span class="small fw-bold"><?= $porcentaje ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-success-subtle text-success border border-success px-3">En Curso</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Configuración Global de Chart.js para un look limpio
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    // Gráfico de Tendencia (Lineal)
    const ctxFlujo = document.getElementById('chartFlujo').getContext('2d');
    new Chart(ctxFlujo, {
        type: 'line',
        data: {
            labels: <?= json_encode($dias) ?>,
            datasets: [
                {
                    label: 'Ingresos',
                    data: <?= json_encode($data_ingresos) ?>,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#16a34a'
                },
                {
                    label: 'Egresos',
                    data: <?= json_encode($data_egresos) ?>,
                    borderColor: '#dc2626',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'end' } },
            scales: {
                y: { grid: { borderDash: [2, 2] }, ticks: { callback: v => '$' + v.toLocaleString() } },
                x: { grid: { display: false } }
            }
        }
    });

    // Gráfico de Segmentación (Doughnut)
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels_pie) ?>,
            datasets: [{
                data: <?= json_encode($data_pie) ?>,
                backgroundColor: ['#1e293b', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                hoverOffset: 15,
                borderWidth: 4,
                borderColor: '#ffffff'
            }]
        },
        options: {
            cutout: '80%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, usePointStyle: true } } }
        }
    });
</script>
</body>
</html>